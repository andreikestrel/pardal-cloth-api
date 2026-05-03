import { computed } from 'vue'
import type { CartCalculation, PromotionApplied } from '@/types'

export function usePromotion(calculation: { value: CartCalculation | null }) {
    const hasPromotions = computed(
        () => (calculation.value?.promotions_applied?.length ?? 0) > 0
    )

    const promotionsSummary = computed<PromotionApplied[]>(
        () => calculation.value?.promotions_applied ?? []
    )

    const totalDiscount = computed(() => {
        const promotions = parseFloat(calculation.value?.discount_promotions ?? '0')
        const coupon = parseFloat(calculation.value?.discount_coupon ?? '0')
        return (promotions + coupon).toFixed(2)
    })

    function formatCurrency(value: string | number): string {
        return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(
            typeof value === 'string' ? parseFloat(value) : value
        )
    }

    return { hasPromotions, promotionsSummary, totalDiscount, formatCurrency }
}
