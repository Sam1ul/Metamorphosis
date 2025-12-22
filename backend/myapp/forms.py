from django import forms
from django.contrib.auth.models import User
from .models import Profile
from django.contrib.auth.forms import AuthenticationForm


class RegistrationForm(forms.ModelForm):
    password = forms.CharField(
        widget=forms.PasswordInput(
            attrs={
                'class': 'form-control bg-black text-primary border border-primary',
                'placeholder': 'Password'
            }
        )
    )
    profile_pic = forms.ImageField(
        required=False,
        widget=forms.FileInput(
            attrs={
                'class': 'form-control bg-black text-primary border border-primary'
            }
        )
    )

    class Meta:
        model = User
        fields = ['username', 'email', 'password']
        widgets = {
            'username': forms.TextInput(
                attrs={
                    'class': 'form-control bg-black text-primary border border-primary',
                    'placeholder': 'Username'
                }
            ),
            'email': forms.EmailInput(
                attrs={
                    'class': 'form-control bg-black text-primary border border-primary',
                    'placeholder': 'Email'
                }
            ),
        }

    def save(self, commit=True):
        user = super().save(commit=False)
        user.set_password(self.cleaned_data['password'])
        if commit:
            user.save()
            # Safely create profile only if it doesn't exist
            profile, created = Profile.objects.get_or_create(user=user)
            if self.cleaned_data.get('profile_pic'):
                profile.profile_pic = self.cleaned_data['profile_pic']
                profile.save()
        return user



class LoginForm(AuthenticationForm):
    username = forms.CharField(
        max_length=150,
        widget=forms.TextInput(
            attrs={
                'class': 'form-control bg-black text-success border border-success',
                'placeholder': 'Username'
            }
        )
    )
    password = forms.CharField(
        widget=forms.PasswordInput(
            attrs={
                'class': 'form-control bg-black text-success border border-success',
                'placeholder': 'Password'
            }
        )
    )
    remember_me = forms.BooleanField(
        required=False,
        widget=forms.CheckboxInput(
            attrs={
                'class': 'form-check-input bg-black text-success border border-success'
            }
        )
    )
