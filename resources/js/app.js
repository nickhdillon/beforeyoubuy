document.addEventListener('alpine:init', () => {
    window.Alpine.data('starRating', ({ model, rating }) => ({
        model,
        rating: Number(rating) || 0,
        hoverRating: null,

        init() {
            this.syncFromLivewire(this.$wire.$get(this.model));

            this.$wire.$watch(this.model, (value) => {
                this.syncFromLivewire(value);
            });
        },

        get displayedRating() {
            return this.hoverRating ?? this.rating;
        },

        preview(value) {
            if (this.rating > 0) {
                return;
            }

            this.hoverRating = value;
        },

        stopPreviewing() {
            this.hoverRating = null;
        },

        select(value) {
            this.rating = value;
            this.hoverRating = null;
            this.$wire.$set(this.model, String(value), false);
        },

        formatRating(value) {
            return Number.isInteger(value) ? String(value) : value.toFixed(1);
        },

        clear() {
            this.rating = 0;
            this.hoverRating = null;
            this.$wire.$set(this.model, '', false);
        },

        syncFromLivewire(value) {
            this.rating = Number(value) || 0;
            this.hoverRating = null;
        },
    }));
});
