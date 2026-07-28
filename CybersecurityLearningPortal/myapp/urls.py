from django.urls import path
from . import views

urlpatterns = [

    # ---------------- Public ----------------
    path('', views.index, name='index'),
    path('index/', views.index, name='index'),
    path('features/', views.features, name='features'),
    path('pricing/', views.pricing, name='pricing'),
    path('initialPage/', views.initialPage, name='initialPage'),

    # ---------------- Authentication ----------------
    path('register/', views.register, name='register'),
    path('login/', views.user_login, name='login'),
    path('logout/', views.user_logout, name='logout'),

    # ---------------- Home ----------------
    path('home/', views.home, name='home'),

    # ---------------- Profile ----------------
    path('profile/', views.profile_view, name='profile'),
    path('profile/<int:user_id>/', views.profile_view_other, name='profile_view_other'),

    # ---------------- Lessons ----------------
    path('sqlInjection/', views.sqlInjection, name='sqlInjection'),
    path('xss/', views.xss, name='xss'),
    path('passw/', views.passw, name='passw'),
    path('csrf/', views.csrf, name='csrf'),
    path('bac/', views.bac, name='bac'),
    path('isd/', views.isd, name='isd'),
    path('af/', views.af, name='af'),
    path('cf/', views.cf, name='cf'),
    path('slmf/', views.slmf, name='slmf'),
    path('ssrf/', views.ssrf, name='ssrf'),

    path(
        'update_lesson_points/',
        views.update_lesson_points,
        name='update_lesson_points'
    ),

    # ---------------- Leaderboard ----------------
    path(
        'leaderboard/',
        views.leaderboard,
        name='leaderboard'
    ),

    # ---------------- Community ----------------
    path(
        'community_chat/',
        views.community_chat,
        name='community_chat'
    ),

    path(
        'get_messages/',
        views.get_messages,
        name='get_messages'
    ),

    # ---------------- Free Downloads ----------------
    path(
        'downloads_page/',
        views.downloads_page,
        name='downloads_page'
    ),

    path(
        'download/create_vuln_bank.sh',
        views.download_vuln_bank_demo,
        name='download_create_vuln_bank'
    ),

    path(
        'download/vuln_bank_demo.zip',
        views.download_vuln_bank_demo,
        name='download_vuln_bank_demo'
    ),

    # ---------------- Game ----------------
    path(
        'update_game1/',
        views.update_game1,
        name='update_game1'
    ),

    # ---------------- Products ----------------

    path(
        'checkout/<slug:slug>/',
        views.checkout,
        name='checkout'
    ),

    path(
        'buy/<slug:slug>/',
        views.buy_product,
        name='buy_product'
    ),

    path(
        'submit-payment/<int:order_id>/',
        views.submit_payment,
        name='submit_payment'
    ),

    path(
        'download/<slug:slug>/',
        views.download_product,
        name='download_product'
    ),
]