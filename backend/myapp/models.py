from django.db import models
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

    # Games: points, ID, name, number
    game1_points = models.IntegerField(default=0)
    game1_id = models.IntegerField(default=0)
    game1_name = models.CharField(max_length=100, default="BlackHydra")
    game1_number = models.IntegerField(default=0)

    game2_points = models.IntegerField(default=0)
    game2_id = models.IntegerField(default=0)
    game2_name = models.CharField(max_length=100, default="Coming Soon")
    game2_number = models.IntegerField(default=0)

    game3_points = models.IntegerField(default=0)
    game3_id = models.IntegerField(default=0)
    game3_name = models.CharField(max_length=100, default="Coming Soon")
    game3_number = models.IntegerField(default=0)

    game4_points = models.IntegerField(default=0)
    game4_id = models.IntegerField(default=0)
    game4_name = models.CharField(max_length=100, default="Coming Soon")
    game4_number = models.IntegerField(default=0)

    game5_points = models.IntegerField(default=0)
    game5_id = models.IntegerField(default=0)
    game5_name = models.CharField(max_length=100, default="Coming Soon")
    game5_number = models.IntegerField(default=0)

    game6_points = models.IntegerField(default=0)
    game6_id = models.IntegerField(default=0)
    game6_name = models.CharField(max_length=100, default="Coming Soon")
    game6_number = models.IntegerField(default=0)

    game7_points = models.IntegerField(default=0)
    game7_id = models.IntegerField(default=0)
    game7_name = models.CharField(max_length=100, default="Coming Soon")
    game7_number = models.IntegerField(default=0)

    game8_points = models.IntegerField(default=0)
    game8_id = models.IntegerField(default=0)
    game8_name = models.CharField(max_length=100, default="Coming Soon")
    game8_number = models.IntegerField(default=0)

    game9_points = models.IntegerField(default=0)
    game9_id = models.IntegerField(default=0)
    game9_name = models.CharField(max_length=100, default="Coming Soon")
    game9_number = models.IntegerField(default=0)

    game10_points = models.IntegerField(default=0)
    game10_id = models.IntegerField(default=0)
    game10_name = models.CharField(max_length=100, default="Coming Soon")
    game10_number = models.IntegerField(default=0)

    # Grand total points (auto-calculated)
    points = models.IntegerField(default=0)

    # Rank: E, D, C, B, A, S
    rank = models.CharField(max_length=2, default='E')

    downloads_count = models.PositiveIntegerField(default=0)

    def save(self, *args, **kwargs):
        """Save Profile, calculate total points, rank, and rename profile picture."""
        # Calculate total points: lessons + games
        self.points = sum([
            self.lesson1_points, self.lesson2_points, self.lesson3_points,
            self.lesson4_points, self.lesson5_points, self.lesson6_points,
            self.lesson7_points, self.lesson8_points, self.lesson9_points,
            self.lesson10_points,
            self.game1_points, self.game2_points, self.game3_points,
            self.game4_points, self.game5_points, self.game6_points,
            self.game7_points, self.game8_points, self.game9_points,
            self.game10_points
        ])

        # --- Ranking System ---
        percentage = (self.points / 2000) * 100  # Assuming max 100 per lesson/game

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
