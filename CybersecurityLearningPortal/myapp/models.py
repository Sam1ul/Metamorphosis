from django.db import models
from decimal import Decimal
from django.contrib.auth.models import User
from django.db.models.signals import post_save
from django.dispatch import receiver
import os


def profile_pic_upload_to(instance, filename):
    """Temporarily save profile picture as 'temp' — renamed after user is saved."""
    return f'profilePics/temp.{filename.split(".")[-1]}'


class Profile(models.Model):
    user = models.OneToOneField(User, on_delete=models.CASCADE)
    profile_pic = models.ImageField(upload_to=profile_pic_upload_to, blank=True, null=True)

    # Points for each lesson
    lesson1_points = models.IntegerField(default=0)
    lesson2_points = models.IntegerField(default=0)
    lesson3_points = models.IntegerField(default=0)
    lesson4_points = models.IntegerField(default=0)
    lesson5_points = models.IntegerField(default=0)
    lesson6_points = models.IntegerField(default=0)
    lesson7_points = models.IntegerField(default=0)
    lesson8_points = models.IntegerField(default=0)
    lesson9_points = models.IntegerField(default=0)
    lesson10_points = models.IntegerField(default=0)

    

    # Grand total points (auto-calculated)
    points = models.IntegerField(default=0)

    # Rank: E, D, C, B, A, S
    rank = models.CharField(max_length=2, default='E')

    downloads_count = models.PositiveIntegerField(default=0)

    def save(self, *args, **kwargs):
        """Save Profile, calculate total points, rank, and rename profile picture."""
        self.points = sum([
            self.lesson1_points,
            self.lesson2_points,
            self.lesson3_points,
            self.lesson4_points,
            self.lesson5_points,
            self.lesson6_points,
            self.lesson7_points,
            self.lesson8_points,
            self.lesson9_points,
            self.lesson10_points,
        ])

        # --- Ranking System ---
        percentage = (self.points / 310) * 100  # Assuming max 100 per lesson/game

        if percentage >= 90:
            self.rank = 'S'
        elif percentage >= 80:
            self.rank = 'A'
        elif percentage >= 70:
            self.rank = 'B'
        elif percentage >= 60:
            self.rank = 'C'
        elif percentage >= 50:
            self.rank = 'D'
        else:
            self.rank = 'E'

        # Save first to ensure instance.user.id exists
        super().save(*args, **kwargs)

        # Rename profile picture to match user ID
        if self.profile_pic:
            old_path = self.profile_pic.path
            ext = old_path.split('.')[-1]
            new_filename = f"{self.user.id}.{ext}"
            new_path = os.path.join(os.path.dirname(old_path), new_filename)

            if old_path != new_path:
                try:
                    os.rename(old_path, new_path)
                    self.profile_pic.name = f"profilePics/{new_filename}"
                    super().save(update_fields=['profile_pic'])
                except FileNotFoundError:
                    pass

    def __str__(self):
        return self.user.username


# --- Signals ---
@receiver(post_save, sender=User)
def create_or_update_user_profile(sender, instance, created, **kwargs):
    """Automatically create or update Profile whenever a User is created or saved."""
    if created:
        Profile.objects.create(user=instance)
    else:
        if hasattr(instance, 'profile'):
            instance.profile.save()


class ChatMessage(models.Model):
    user = models.ForeignKey(User, on_delete=models.CASCADE)
    message = models.TextField(blank=True, null=True)
    image = models.ImageField(upload_to='chat_images/', blank=True, null=True)
    timestamp = models.DateTimeField(auto_now_add=True)

    class Meta:
        ordering = ['-timestamp']

    def __str__(self):
        return f"{self.user.username}: {self.message[:30]}"






class Product(models.Model):
    name = models.CharField(max_length=100)

    slug = models.SlugField(unique=True)

    description = models.TextField(blank=True)

    price = models.DecimalField(max_digits=8, decimal_places=2)

    file = models.FileField(upload_to="products/")

    active = models.BooleanField(default=True)

    def __str__(self):
        return self.name


class Order(models.Model):

    STATUS = (
        ("pending", "Pending"),
        ("paid", "Paid"),
        ("failed", "Failed"),
        ("cancelled", "Cancelled"),
    )

    user = models.ForeignKey(
        User,
        on_delete=models.CASCADE,
        related_name="orders"
    )

    product = models.ForeignKey(
        Product,
        on_delete=models.CASCADE
    )

    amount = models.DecimalField(
        max_digits=10,
        decimal_places=2
    )

    status = models.CharField(
        max_length=20,
        choices=STATUS,
        default="pending"
    )

    created_at = models.DateTimeField(auto_now_add=True)

    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        ordering = ["-created_at"]

    def __str__(self):
        return f"{self.user.username} - {self.product.name}"

class Payment(models.Model):

    PAYMENT_STATUS = (
        ("pending", "Pending Verification"),
        ("verified", "Verified"),
        ("rejected", "Rejected"),
    )

    order = models.OneToOneField(
        Order,
        on_delete=models.CASCADE,
        related_name="payment"
    )

    gateway = models.CharField(
        max_length=30,
        default="bKash Manual"
    )

    payment_id = models.CharField(
        max_length=100,
        unique=True
    )

    # User submitted information
    sender_number = models.CharField(
        max_length=15,
        blank=True,
        null=True
    )

    trx_id = models.CharField(
        max_length=50,
        blank=True,
        null=True
    )

    screenshot = models.ImageField(
        upload_to="payment_screenshots/",
        blank=True,
        null=True
    )

    amount = models.DecimalField(
        max_digits=10,
        decimal_places=2
    )

    status = models.CharField(
        max_length=20,
        choices=PAYMENT_STATUS,
        default="pending"
    )

    admin_note = models.TextField(
        blank=True,
        null=True
    )

    currency = models.CharField(
        max_length=10,
        default="BDT"
    )

    paid_at = models.DateTimeField(
        blank=True,
        null=True
    )

    created_at = models.DateTimeField(
        auto_now_add=True
    )

    class Meta:
        ordering = ["-created_at"]

    def __str__(self):
        return f"{self.order.user.username} - {self.order.product.name}"