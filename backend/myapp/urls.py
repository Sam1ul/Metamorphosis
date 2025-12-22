from django.urls import path
from . import views

urlpatterns = [
    path('', views.index, name='index'),
    path('index/', views.index, name='index'),
    path('features/', views.features, name='features'),
    path('pricing/', views.pricing, name='pricing'),
    path('initialPage/', views.initialPage, name='initialPage'),
    path('register/', views.register, name='register'),
    path('login/', views.user_login, name='login'),
    path('logout/', views.user_logout, name='logout'),
    path('profile/', views.profile_view, name='profile'),
    path('home/', views.home, name='home'),

    path('sqlInjection/', views.sqlInjection, name='sqlInjection'),

    path('xss/', views.xss, name='xss'),
    path('passw/', views.passw, name='passw'),
    path('csrf/', views.csrf, name='csrf'),

    # ✅ New route for lesson score submission (AJAX)
    path('update_lesson_points/', views.update_lesson_points, name='update_lesson_points'),

    path('leaderboard/', views.leaderboard, name='leaderboard'),

    # Add this below your profile route
    path('profile/<int:user_id>/', views.profile_view_other, name='profile_view_other'),

    path('community_chat/', views.community_chat, name='community_chat'),

    path('get_messages/', views.get_messages, name='get_messages'),
    # Page that lists download(s)
    path("downloads_page/", views.downloads_page, name="downloads_page"),

    # Download routes
    path("download/create_vuln_bank.sh", views.download_vuln_bank_demo, name="download_create_vuln_bank"),
    path("download/vuln_bank_demo.zip", views.download_vuln_bank_demo, name="download_vuln_bank_demo"),







    # Add this to urlpatterns
    path('update_game1/', views.update_game1, name='update_game1'),

]
