<?php
function render_testimonial_carousel_block( $attributes ) {
    // Extract attributes from block
    $carouselGroup = $attributes['carouselGroup'] ?? '';
    $carouselDesign = $attributes['carouselDesign'] ?? 'simple';
    $autoplay = $attributes['autoplay'] ?? true; // Autoplay setting from block.json
    $titleColor = $attributes['titleColor'] ?? '';
    $contentColor = $attributes['contentColor'] ?? '';
    $starColor = $attributes['starColor'] ?? 'gold';
    $gradientColor1 = $attributes['gradientColor1'] ?? '#6a11cb';
    $gradientColor2 = $attributes['gradientColor2'] ?? '#2575fc';
    $solidBgColor = $attributes['solidBgColor'] ?? '#f3f4f6';
    $glassOpacity = $attributes['glassOpacity'] ?? 0.7;
    $readMoreColor = $attributes['readMoreColor'] ?? '#007cba';

    // Fetch testimonials for the selected group
    $args = [
        'post_type' => 'testimonial',
        'tax_query' => [
            [
                'taxonomy' => 'carousel_group',
                'field'    => 'term_id',
                'terms'    => $carouselGroup,
            ],
        ],
    ];
    $testimonials = get_posts( $args );

    if ( empty( $testimonials ) ) {
        return '<p>No testimonials available.</p>';
    }

    // Determine design style
    $carouselStyle = '';
    if ( $carouselDesign === 'gradient' ) {
        $carouselStyle = "background: linear-gradient(to right, $gradientColor1, $gradientColor2); border-radius: 15px;";
    } elseif ( $carouselDesign === 'solid' ) {
        $carouselStyle = "background-color: $solidBgColor; border-radius: 15px; opacity: $glassOpacity;";
    } elseif ( $carouselDesign === 'simple' ) {
        $carouselStyle = "border-radius: 15px;";
    }

    ob_start(); ?>
    <div class="testimonial-carousel-container" style="<?php echo esc_attr( $carouselStyle ); ?>">
        <div class="testimonial-card">
            <button class="arrow-button left-arrow" onclick="prevTestimonial()">&lt;</button>
            <div class="testimonial-card-left">
                <img class="avatar" src="<?php echo esc_url( get_the_post_thumbnail_url( $testimonials[0]->ID, 'thumbnail' ) ?: 'https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp&f=y' ); ?>" alt="<?php echo esc_attr( $testimonials[0]->post_title ); ?>">
            </div>
            <div class="testimonial-card-right">
                <h4 class="testimonial-name" style="color: <?php echo esc_attr( $titleColor ); ?>;">
                    <?php echo esc_html( $testimonials[0]->post_title ); ?>
                </h4>
                <div class="testimonial-rating-container">
                    <?php
                    $rating = get_post_meta( $testimonials[0]->ID, 'testimonial_rating', true ) ?: 0;
                    for ( $i = 0; $i < 5; $i++ ) {
                        echo '<span class="' . ( $i < $rating ? 'filled-star' : 'empty-star' ) . '" style="color: ' . esc_attr( $starColor ) . ';">★</span>';
                    }
                    ?>
                </div>
                <div class="testimonial-description" style="color: <?php echo esc_attr( $contentColor ); ?>;">
                    <?php echo wp_kses_post( $testimonials[0]->post_content ); ?>
                </div>
                <button class="read-more" style="color: <?php echo esc_attr( $readMoreColor ); ?>;" onclick="toggleReadMore(this)">Read More</button>
            </div>
            <button class="arrow-button right-arrow" onclick="nextTestimonial()">&gt;</button>
        </div>
    </div>
    <script>
        let currentIndex = 0;
        const testimonials = <?php echo wp_json_encode( array_map( function( $testimonial ) {
            return [
                'title' => $testimonial->post_title,
                'content' => wp_strip_all_tags( $testimonial->post_content ),
                'featured_image' => get_the_post_thumbnail_url( $testimonial->ID, 'thumbnail' ),
                'rating' => get_post_meta( $testimonial->ID, 'testimonial_rating', true ) ?: 0,
            ];
        }, $testimonials ) ); ?>;

        const autoplayEnabled = <?php echo json_encode( $autoplay ); ?>;
        const interval = 5000; // Autoplay interval in milliseconds
        let autoplayTimer;

        function nextTestimonial() {
            currentIndex = (currentIndex + 1) % testimonials.length;
            updateTestimonial();
        }

        function prevTestimonial() {
            currentIndex = (currentIndex - 1 + testimonials.length) % testimonials.length;
            updateTestimonial();
        }

        function toggleReadMore(button) {
            const description = button.previousElementSibling;
            if (description.style.maxHeight) {
                description.style.maxHeight = null;
                button.textContent = 'Read More';
            } else {
                description.style.maxHeight = description.scrollHeight + 'px';
                button.textContent = 'Read Less';
            }
        }

        function updateTestimonial() {
            const testimonial = testimonials[currentIndex];
            document.querySelector('.testimonial-card-left .avatar').src = testimonial.featured_image || 'https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp&f=y';
            document.querySelector('.testimonial-name').textContent = testimonial.title;
            document.querySelector('.testimonial-description').textContent = testimonial.content;
            const stars = document.querySelector('.testimonial-rating-container');
            stars.innerHTML = '';
            for (let i = 0; i < 5; i++) {
                stars.innerHTML += `<span class="${i < testimonial.rating ? 'filled-star' : 'empty-star'}" style="color: ${<?php echo wp_json_encode( $starColor ); ?>};">★</span>`;
            }
        }

        function startAutoplay() {
            if (autoplayEnabled) {
                autoplayTimer = setInterval(nextTestimonial, interval);
            }
        }

        function stopAutoplay() {
            clearInterval(autoplayTimer);
        }

        // Start autoplay on load if enabled
        document.addEventListener('DOMContentLoaded', () => {
            startAutoplay();

            // Pause autoplay on hover
            const container = document.querySelector('.testimonial-carousel-container');
            container.addEventListener('mouseenter', stopAutoplay);
            container.addEventListener('mouseleave', startAutoplay);
        });
    </script>
    <?php
    return ob_get_clean();
}
