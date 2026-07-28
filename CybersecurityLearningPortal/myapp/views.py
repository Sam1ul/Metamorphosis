from django.shortcuts import render, redirect, get_object_or_404
from django.contrib.auth import authenticate, login, logout
from django.contrib import messages
from django.contrib.auth.decorators import login_required
from django.contrib.auth.models import User
from django.http import JsonResponse, response
from django.views.decorators.csrf import csrf_exempt
from .forms import RegistrationForm, LoginForm

import json

from .models import (
    Order,
    Payment,
    Product,
    Profile,
    ChatMessage,
)
 
# ---------------- Public Pages ----------------
def index(request):
    return render(request, 'myapp/index.html')

from django.shortcuts import get_object_or_404

@login_required(login_url="login")
def pricing(request):

    pro = Product.objects.get(slug="pro")
    enterprise = Product.objects.get(slug="enterprise")

    context = {
        "pro": pro,
        "enterprise": enterprise,
    }

    if request.user.is_authenticated:

        pro_paid = Order.objects.filter(
            user=request.user,
            product=pro,
            status="paid"
        ).exists()

        pro_pending = Order.objects.filter(
            user=request.user,
            product=pro,
            status="pending"
        ).first()

        enterprise_paid = Order.objects.filter(
            user=request.user,
            product=enterprise,
            status="paid"
        ).exists()

        enterprise_pending = Order.objects.filter(
            user=request.user,
            product=enterprise,
            status="pending"
        ).first()

        context.update({
            "pro_paid": pro_paid,
            "pro_pending": pro_pending,
            "enterprise_paid": enterprise_paid,
            "enterprise_pending": enterprise_pending,
        })

    return render(request, "myapp/pricing.html", context)

    
def features(request):
    return render(request, 'myapp/features.html')

def initialPage(request):
    return render(request, 'myapp/initialPage.html')


# ---------------- Authenticated Pages ----------------
@login_required(login_url='login')
def home(request):
    return render(request, 'myapp/home.html', {'show_search': True})

@login_required(login_url='login')
def sqlInjection(request):
    return render(request, 'myapp/sqlInjection.html', {
        'show_search': True,
        'lesson_number': 1,
    })

@login_required(login_url='login')
def xss(request):
    return render(request, 'myapp/xss.html', {
        'show_search': True,
        'lesson_number': 2,
    })

@login_required(login_url='login')
def passw(request):
    return render(request, 'myapp/password.html', {
        'show_search': True,
        'lesson_number': 3,
    })

@login_required(login_url='login')
def csrf(request):
    return render(request, 'myapp/csrf.html', {
        'show_search': True,
        'lesson_number': 4,
    })

@login_required(login_url='login')
def bac(request):
    return render(request, 'myapp/bac.html', {
        'show_search': True,
        'lesson_number': 5,
    })

@login_required(login_url='login')
def isd(request):
    return render(request, 'myapp/isd.html', {
        'show_search': True,
        'lesson_number': 6,
    })

@login_required(login_url='login')
def af(request):
    return render(request, 'myapp/af.html', {
        'show_search': True,
        'lesson_number': 7,
    })

@login_required(login_url='login')
def cf(request):
    return render(request, 'myapp/cf.html', {
        'show_search': True,
        'lesson_number': 8,
    })

@login_required(login_url='login')
def slmf(request):
    return render(request, 'myapp/slmf.html', {
        'show_search': True,
        'lesson_number': 9,
    })

@login_required(login_url='login')
def ssrf(request):
    return render(request, 'myapp/ssrf.html', {
        'show_search': True,
        'lesson_number': 10,
    })

# ---------------- User Auth ----------------
def register(request):
    if request.method == "POST":
        form = RegistrationForm(request.POST, request.FILES)
        if form.is_valid():
            user = form.save()  # Create the User instance

            # Safely create Profile if it doesn't exist
            Profile.objects.get_or_create(
                user=user,
                defaults={'profile_pic': form.cleaned_data.get('profile_pic')}
            )

            messages.success(request, "Registration successful. Please log in.")
            return redirect("login")
        else:
            messages.error(request, "Registration failed. Please check the form.")
    else:
        form = RegistrationForm()
    return render(request, "myapp/register.html", {"form": form})


def user_login(request):
    if request.method == 'POST':
        form = LoginForm(request, data=request.POST)
        if form.is_valid():
            user = form.get_user()
            login(request, user)

            # Handle "Remember Me"
            if form.cleaned_data.get('remember_me'):
                request.session.set_expiry(60 * 60 * 24 * 30)  # 30 days
            else:
                request.session.set_expiry(0)  # Browser close

            messages.success(request, f"Welcome, {user.username}!")
            next_url = request.GET.get('next', 'home')
            return redirect(next_url)
        else:
            messages.error(request, "Invalid username or password.")
    else:
        form = LoginForm()
    return render(request, 'myapp/login.html', {'form': form})


def user_logout(request):
    logout(request)
    messages.success(request, "You have been logged out.")
    return redirect('login')


# ---------------- Profile Pages ----------------
@login_required(login_url='login')
def profile_view(request):
    """View your own profile"""
    profile_user = request.user
    return render(request, 'myapp/profile.html', {'profile_user': profile_user})


@login_required(login_url='login')
def profile_view_other(request, user_id):
    """View another user's profile"""
    profile_user = get_object_or_404(User, id=user_id)
    return render(request, 'myapp/profile.html', {'profile_user': profile_user})


# ---------------- Leaderboard ----------------
@login_required(login_url='login')
def leaderboard(request):
    profiles = Profile.objects.select_related('user').order_by('-points')

    ranked_profiles = []
    for idx, profile in enumerate(profiles, start=1):
        ranked_profiles.append({
            'rank': idx,
            'user_id': profile.user.id,
            'username': profile.user.username,
            'points': profile.points,
            'profile_pic': profile.profile_pic.url if profile.profile_pic else None,
        })

    return render(request, 'myapp/leaderboard.html', {'profiles': ranked_profiles})


# ---------------- Lesson Points Update (AJAX) ----------------
@login_required(login_url='login')
@csrf_exempt
def update_lesson_points(request):
    if request.method == "POST":
        try:
            data = json.loads(request.body or "{}")
            lesson_raw = data.get("lesson")
            score_raw = data.get("score")

            if lesson_raw is None:
                return JsonResponse({"success": False, "error": "Missing 'lesson' in request."}, status=400)

            try:
                lesson_num = int(lesson_raw)
            except ValueError:
                return JsonResponse({"success": False, "error": f"Invalid lesson value: {lesson_raw}"}, status=400)

            if not 1 <= lesson_num <= 10:
                return JsonResponse({"success": False, "error": "Invalid lesson number range (1–10)."}, status=400)

            try:
                score = int(score_raw)
            except (ValueError, TypeError):
                score = 0

            profile = request.user.profile
            field_name = f"lesson{lesson_num}_points"

            if not hasattr(profile, field_name):
                return JsonResponse({"success": False, "error": f"Profile field '{field_name}' not found."}, status=400)

            setattr(profile, field_name, score)
            profile.save()

            return JsonResponse({
                "success": True,
                "message": f"Lesson {lesson_num} points updated successfully.",
                "lesson_points": score,
                "total_points": profile.points
            })

        except json.JSONDecodeError:
            return JsonResponse({"success": False, "error": "Invalid JSON payload."}, status=400)
        except Exception as e:
            return JsonResponse({"success": False, "error": f"Unexpected error: {str(e)}"}, status=400)

    return JsonResponse({"success": False, "error": "Invalid request method."}, status=405)


# ---------------- Community Chat ----------------
@login_required(login_url='login')
def community_chat(request):

    if request.method == 'POST':
        message = request.POST.get('message')
        image = request.FILES.get('image')

        if message or image:
            ChatMessage.objects.create(
                user=request.user,
                message=message,
                image=image
            )

        return JsonResponse({"success": True})

    messages = ChatMessage.objects.select_related('user').order_by('timestamp')

    return render(
        request,
        'myapp/community_chat.html',
        {'messages': messages}
    )
@login_required(login_url='login')
def get_messages(request):

    messages = ChatMessage.objects.select_related("user").order_by("timestamp")

    data = []

    for msg in messages:
        data.append({
            "id": msg.id,
            "username": msg.user.username,
            "user_id": msg.user.id,
            "profile_pic": msg.user.profile.profile_pic.url if msg.user.profile.profile_pic else None,
            "message": msg.message,
            "image": msg.image.url if msg.image else None,
            "timestamp": msg.timestamp.strftime("%b %d, %Y %H:%M")
        })

    return JsonResponse({"messages": data})

# ---------------- Downloads ----------------
import os
import mimetypes
from django.conf import settings
from django.http import FileResponse, Http404, HttpResponseForbidden
from django.views.decorators.http import require_GET
from django.utils.encoding import smart_str
from django.contrib.auth.decorators import login_required
from django.shortcuts import render

# Absolute path to protected files
PROTECTED_DIR = os.path.join(settings.BASE_DIR, "protected_files")

@login_required(login_url='login')
def downloads_page(request):
    """Simple page listing available downloads."""
    return render(request, "myapp/downloads.html", {"filename": "vuln_bank_demo.zip"})

@login_required(login_url='login')
@require_GET
def download_vuln_bank_demo(request):
    """Serve the ZIP file securely."""
    filename = "vuln_bank_demo.zip"
    file_path = os.path.abspath(os.path.join(PROTECTED_DIR, filename))
    protected_dir_abs = os.path.abspath(PROTECTED_DIR)

    # Prevent directory traversal attacks
    if not file_path.startswith(protected_dir_abs + os.sep):
        return HttpResponseForbidden("Forbidden")

    if not os.path.exists(file_path) or not os.path.isfile(file_path):
        raise Http404("File not found")

    # Optional: Increment user's download count
    try:
        profile = request.user.profile
        if hasattr(profile, "downloads_count"):
            profile.downloads_count = getattr(profile, "downloads_count", 0) + 1
            profile.save(update_fields=["downloads_count"])
    except Exception:
        pass  # fail silently if user profile does not exist

    mime_type, _ = mimetypes.guess_type(file_path)
    if mime_type is None:
        mime_type = "application/octet-stream"

    fh = open(file_path, "rb")
    response = FileResponse(fh, content_type=mime_type)
    response["Content-Length"] = os.path.getsize(file_path)
    response["Content-Disposition"] = f'attachment; filename="{smart_str(filename)}"'
    return response




from django.views.decorators.http import require_http_methods

@login_required(login_url='login')
@require_http_methods(["GET", "POST"])
def update_game1(request):
    """
    Update game1 for user when typing 'Victor Lang' (any case)
    """
    message = ""
    if request.method == "POST":
        input_name = request.POST.get("username", "").strip()
        
        if input_name.lower() == "victor lang":
            try:
                user = request.user  # Current logged-in user
                profile = user.profile
                profile.game1_name = "BlackHydra"
                profile.game1_points = 100  # ✅ Set the points
                profile.save()  # ✅ Recalculates total points
                message = "Good Lord, we found massive connection of him with Black Hydra and he was in jail for several years for human trafficking. We will catch him for sure. Good job, 100 points awarded to you!"
            except Exception as e:
                message = f"Error updating game1: {str(e)}"
        else:
            message = "We have not found any connection with the name you provided. Please try again, son."

    return render(request, "myapp/update_game1.html", {"message": message})


from uuid import uuid4
from django.shortcuts import get_object_or_404
from django.http import FileResponse
from django.contrib.auth.decorators import login_required


@login_required
def checkout(request, slug):

    product = get_object_or_404(
        Product,
        slug=slug,
        active=True
    )

    already_bought = Order.objects.filter(
        user=request.user,
        product=product,
        status="paid"
    ).exists()

    pending_order = Order.objects.filter(
        user=request.user,
        product=product,
        status="pending"
    ).first()

    return render(
        request,
        "myapp/checkout.html",
        {
            "product": product,
            "already_bought": already_bought,
            "pending_order": pending_order,
        },
    )

@login_required
def buy_product(request, slug):

    product = get_object_or_404(
        Product,
        slug=slug,
        active=True
    )

    # User already owns the product
    already_paid = Order.objects.filter(
        user=request.user,
        product=product,
        status="paid"
    ).first()

    if already_paid:
        return redirect(
            "download_product",
            slug=slug
        )

    # Existing pending order
    existing_order = Order.objects.filter(
        user=request.user,
        product=product,
        status="pending"
    ).first()

    if existing_order:
        return redirect(
            "submit_payment",
            order_id=existing_order.id
        )

    # Create new order
    order = Order.objects.create(
        user=request.user,
        product=product,
        amount=product.price,
        status="pending",
    )

    Payment.objects.create(
        order=order,
        gateway="bKash Manual",
        payment_id=str(uuid4()),
        amount=product.price,
        status="pending",
    )

    return redirect(
        "submit_payment",
        order_id=order.id
    )

@login_required
def submit_payment(request, order_id):

    order = get_object_or_404(
        Order,
        id=order_id,
        user=request.user,
    )

    payment = order.payment

    # Already verified? Allow download.
    if payment.status == "verified":
        return redirect("download_product", slug=order.product.slug)

    if request.method == "POST":

        # Prevent duplicate submissions after verification/rejection
        if payment.status != "pending":
            messages.warning(
                request,
                "This payment has already been processed."
            )
            return redirect("submit_payment", order_id=order.id)

        payment.sender_number = request.POST.get(
            "sender_number", ""
        ).strip()

        payment.trx_id = request.POST.get(
            "trx_id", ""
        ).strip()

        if request.FILES.get("screenshot"):
            payment.screenshot = request.FILES["screenshot"]

        payment.save()

        messages.success(
            request,
            "Payment details submitted successfully. Please wait for admin verification."
        )

        return redirect("submit_payment", order_id=order.id)

    return render(
        request,
        "myapp/submit_payment.html",
        {
            "order": order,
            "payment": payment,
            "bkash_number": "01797159485",  # Demo number
        }
    )

@login_required
def download_product(request, slug):

    product = get_object_or_404(
        Product,
        slug=slug,
        active=True,
    )

    purchased = Order.objects.filter(
        user=request.user,
        product=product,
        status="paid",
    ).exists()

    if not purchased:
        return HttpResponseForbidden(
            "Please purchase this product first."
        )

    return FileResponse(
        product.file.open("rb"),
        as_attachment=True,
        filename=os.path.basename(product.file.name),
    )