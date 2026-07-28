from django.contrib import admin
from django.utils import timezone
from .models import Product, Order, Payment


@admin.register(Product)
class ProductAdmin(admin.ModelAdmin):
    list_display = ("name", "price", "active")
    prepopulated_fields = {"slug": ("name",)}


@admin.register(Order)
class OrderAdmin(admin.ModelAdmin):
    list_display = (
        "id",
        "user",
        "product",
        "amount",
        "status",
        "created_at",
    )

    list_filter = (
        "status",
        "created_at",
    )

    search_fields = (
        "user__username",
        "product__name",
    )


@admin.register(Payment)
class PaymentAdmin(admin.ModelAdmin):

    list_display = (
        "payment_id",
        "order",
        "sender_number",
        "trx_id",
        "amount",
        "status",
        "created_at",
    )

    list_filter = (
        "status",
        "gateway",
    )

    search_fields = (
        "payment_id",
        "trx_id",
        "sender_number",
        "order__user__username",
    )

    readonly_fields = (
        "payment_id",
        "created_at",
    )

    actions = [
        "verify_payments",
        "reject_payments",
    ]

    def verify_payments(self, request, queryset):

        for payment in queryset:

            payment.status = "verified"
            payment.paid_at = timezone.now()
            payment.save()

            payment.order.status = "paid"
            payment.order.save()

        self.message_user(
            request,
            f"{queryset.count()} payment(s) verified."
        )

    verify_payments.short_description = "✅ Verify selected payments"

    def reject_payments(self, request, queryset):

        for payment in queryset:

            payment.status = "rejected"
            payment.save()

            payment.order.status = "failed"
            payment.order.save()

        self.message_user(
            request,
            f"{queryset.count()} payment(s) rejected."
        )

    reject_payments.short_description = "❌ Reject selected payments"