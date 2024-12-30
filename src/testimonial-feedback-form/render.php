<?php
// This function renders the testimonial form block on the frontend
function render_testimonial_form( $attributes ) {
    // Extract attributes passed to the block
    $testimonial_name = isset( $attributes['name'] ) ? $attributes['name'] : '';
    $testimonial_email = isset( $attributes['email'] ) ? $attributes['email'] : '';
    $testimonial_experience = isset( $attributes['experience'] ) ? $attributes['experience'] : '';
    $testimonial_rating = isset( $attributes['rating'] ) ? $attributes['rating'] : 0;
    $testimonial_category = isset( $attributes['category'] ) ? $attributes['category'] : '';
    $testimonial_image_url = isset( $attributes['image'] ) ? $attributes['image'] : '';

    // Create a unique ID for each block instance (to avoid duplication in case of multiple blocks)
    $block_id = uniqid( 'testimonial-form-' );

    // Enqueue the styles for the frontend (use the block editor styles)
    wp_enqueue_style( 'testimonial-form-block-styles', plugin_dir_url( __FILE__ ) . 'index.css' );

    // Output the HTML for the block (pixel-perfect version)
    ob_start();
    ?>
    <div id="<?php echo esc_attr( $block_id ); ?>" class="testimonial-form">
        <form id="testimonial-form-<?php echo esc_attr( $block_id ); ?>" method="POST" enctype="multipart/form-data">
            <?php wp_nonce_field( 'testimonial_form_nonce', 'testimonial_nonce' ); ?>
            <div class="testimonial-form-field">
                <input
                    type="text"
                    id="<?php echo esc_attr( $block_id . '-name' ); ?>"
                    name="name"
                    value="<?php echo esc_attr( $testimonial_name ); ?>"
                    class="components-text-control"
                    placeholder="Your Name"
                    style="background-color: #fff; border: 1px solid #d0a0d6; border-radius: 4px; padding: 12px 16px; margin-bottom: 8px; font-size: 14px; font-family: 'Inter', sans-serif; color: #333; width: 100%; box-sizing: border-box; height: 35px; placeholder-color: #9e9e9e; text-align: left;"
                    required
                />
            </div>

            <div class="testimonial-form-field">
                <input
                    type="email"
                    id="<?php echo esc_attr( $block_id . '-email' ); ?>"
                    name="email"
                    value="<?php echo esc_attr( $testimonial_email ); ?>"
                    class="components-text-control"
                    placeholder="Your Email"
                    style="background-color: #fff; border: 1px solid #d0a0d6; border-radius: 4px; padding: 12px 16px; margin-bottom: 8px; font-size: 14px; font-family: 'Inter', sans-serif; color: #333; width: 100%; box-sizing: border-box; height: 35px; placeholder-color: #9e9e9e; text-align: left;"
                    required
                />
            </div>

            <div class="testimonial-form-field">
                <textarea
                    id="<?php echo esc_attr( $block_id . '-experience' ); ?>"
                    name="experience"
                    class="components-textarea-control"
                    rows="4"
                    placeholder="Tell us about your experience"
                    style="background-color: #fff; border: 1px solid #d0a0d6; border-radius: 4px; padding: 12px 16px; margin-bottom: 8px; font-size: 14px; font-family: 'Inter', sans-serif; color: #333; width: 100%; box-sizing: border-box; height: 100px; placeholder-color: #9e9e9e; text-align: left;"
                    required
                ><?php echo esc_textarea( $testimonial_experience ); ?></textarea>
            </div>

            <div class="testimonial-form-field rating-stars" style='margin-bottom: 0;'>
                <div class="rating-stars" id="<?php echo esc_attr( $block_id . '-rating' ); ?>">
                    <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                        <span
                            class="star"
                            data-value="<?php echo $i; ?>"
                            style="color: #DDD; transition: color 0.2s;"
                        >
                            ★
                        </span>
                    <?php endfor; ?>
                    <input type="hidden" name="rating" id="<?php echo esc_attr( $block_id . '-rating-input' ); ?>" value="<?php echo esc_attr( $testimonial_rating ); ?>">
                </div>
            </div>

            <!-- Drop Zone for Image Upload -->
            <div
                id="drop-zone-<?php echo esc_attr( $block_id ); ?>"
                class="drag-and-drop-area"
                style="
                    margin-top: 0;
                    border: 2px dashed #ddd;
                    border-radius: 8px;
                    padding: 10px;
                    text-align: center;
                    margin-bottom: 20px;
                    position: relative;
                    background-color: #fff;
                    min-height: 80px;
                    display: flex;
                    align-items: center;
                    justify-content: center;"
                ondrop="handleDrop(event, '<?php echo esc_js( $block_id ); ?>')"
                ondragover="handleDragOver(event, '<?php echo esc_js( $block_id ); ?>')"
                ondragleave="handleDragLeave(event, '<?php echo esc_js( $block_id ); ?>')"
            >
                <p id="drop-zone-text-<?php echo esc_attr( $block_id ); ?>"
                   style="margin: 0;">Drag & Drop an image here.</p>
                <img id="preview-<?php echo esc_attr( $block_id ); ?>"
                     src="<?php echo esc_url( $testimonial_image_url ); ?>"
                     alt="Image Preview"
                     style="display: none; width: 100px; height: 100px; object-fit: cover; border-radius: 8px; margin: 0;">
            </div>

            <input type="file" id="file-input-<?php echo esc_attr( $block_id ); ?>" style="display: none;" accept="image/*" onchange="handleFileSelect(event, '<?php echo esc_js( $block_id ); ?>')">

            <div style="display: flex; justify-content: space-between; margin-top: 20px; border-radius: 8px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
                <button 
                    type="button" 
                    class="add-picture-btn" 
                    style="width: 100%; height: 40px; border: 1px solid #00bfae; font-size: 14px; cursor: pointer; transition: background-color 0.3s ease;"
                    onclick="openFileDialog();"
                >
                    <?php esc_html_e( 'Add Picture', 'testimonial-form' ); ?>
                </button>
                <button 
                    type="submit" 
                    class="submit-btn" 
                    style="width: 100%; height: 40px; border: 1px solid #00bfae; font-size: 14px; cursor: pointer;"
                    name="submit"
                >
                    <?php esc_html_e( 'Submit', 'testimonial-form' ); ?>
                </button>
            </div>
        </form>
    </div>

    <script>
        function openFileDialog() {
            const fileInput = document.getElementById('file-input-<?php echo esc_js( $block_id ); ?>');
            fileInput.click();
        }

        document.addEventListener('DOMContentLoaded', function () {
            const stars = document.querySelectorAll('#<?php echo esc_js( $block_id . "-rating" ); ?> .star');
            const ratingInput = document.getElementById('<?php echo esc_js( $block_id . "-rating-input" ); ?>');
            let rating = <?php echo esc_js( $testimonial_rating ); ?>;

            stars.forEach(star => {
                const starValue = parseInt(star.getAttribute('data-value'));

                // Hover effect: Change color for all stars up to the hovered star
                star.addEventListener('mouseenter', function () {
                    setStarColors(starValue);
                });

                // Restore persistent rating on mouse leave
                star.addEventListener('mouseleave', function () {
                    setStarColors(rating);
                });

                // Persistent selection on click
                star.addEventListener('click', function () {
                    rating = starValue; // Update persistent rating
                    ratingInput.value = rating; // Set the hidden input value
                    setStarColors(rating); // Update star colors
                });
            });

            // Helper function to set star colors up to a specific value
            function setStarColors(value) {
                stars.forEach(s => {
                    const sValue = parseInt(s.getAttribute('data-value'));
                    if (sValue <= value) {
                        s.style.color = '#FFD700'; // Gold for selected stars
                    } else {
                        s.style.color = '#DDD'; // Gray for unselected stars
                    }
                });
            }

            // Initial rendering for persistent rating
            setStarColors(rating);
        });
        function handleDragOver(event, blockId) {
    event.preventDefault();
    const dropZone = document.getElementById('drop-zone-' + blockId);
    const dropZoneText = document.getElementById('drop-zone-text-' + blockId);
    
    dropZone.style.backgroundColor = '#eaf7e1'; // Light green background when dragging over
    dropZoneText.innerText = 'Drop it here'; // Change the text when dragging
}

function handleDragLeave(event, blockId) {
    event.preventDefault();
    const dropZone = document.getElementById('drop-zone-' + blockId);
    const dropZoneText = document.getElementById('drop-zone-text-' + blockId);
    
    dropZone.style.backgroundColor = '#fff'; // Reset the background color
    dropZoneText.innerText = 'Drag & Drop an image here.'; // Reset the text when dragging leaves
}
function handleDrop(event, blockId) {
    event.preventDefault();
    const dropZone = document.getElementById('drop-zone-' + blockId);
    const preview = document.getElementById('preview-' + blockId);
    const dropZoneText = document.getElementById('drop-zone-text-' + blockId);
    const fileInput = document.getElementById('file-input-' + blockId);

    const files = event.dataTransfer.files;
    if (files.length > 0) {
        const file = files[0];
        const reader = new FileReader();

        reader.onload = function (e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            dropZoneText.style.display = 'none';

            // Set the file to the hidden file input to make sure it gets submitted
            const dataTransfer = new DataTransfer(); // Create a new DataTransfer object
            dataTransfer.items.add(file); // Add the dragged file
            fileInput.files = dataTransfer.files; // Assign it to the hidden input
        };
        reader.readAsDataURL(file);
    }

    dropZone.style.backgroundColor = '#fff'; // Reset background color
}

function handleFileSelect(event, blockId) {
    const fileInput = event.target;
    const preview = document.getElementById('preview-' + blockId);
    const dropZoneText = document.getElementById('drop-zone-text-' + blockId);

    const file = fileInput.files[0];
    if (file) {
        const reader = new FileReader();

        reader.onload = function (e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            dropZoneText.style.display = 'none';
        };
        reader.readAsDataURL(file);
    }
}

document.getElementById('testimonial-form-<?php echo esc_attr( $block_id ); ?>').addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    // Get the file input and convert the image to base64 if present
    const fileInput = document.getElementById('file-input-<?php echo esc_js( $block_id ); ?>');
    if (fileInput.files.length > 0) {
        const file = fileInput.files[0];

        // Convert the file to a base64 string
        const reader = new FileReader();
        reader.onloadend = function () {
            // Append the base64 image data to the FormData
            formData.append('image_data', reader.result);

            // Proceed with the form submission once the image is converted
            submitTestimonial(formData);
        };
        reader.readAsDataURL(file);
    } else {
        // If no file, proceed with form submission
        submitTestimonial(formData);
    }
});

function submitTestimonial(formData) {
    formData.append('action', 'submit_testimonial'); // Add action for the backend

    fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
        method: 'POST',
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Testimonial submitted successfully!');
            // Reset the form
            const form = document.getElementById('testimonial-form-<?php echo esc_attr( $block_id ); ?>');
            form.reset();

            // Reset image preview and other elements
            const preview = document.getElementById('preview-<?php echo esc_attr( $block_id ); ?>');
            preview.style.display = 'none';
            const dropZoneText = document.getElementById('drop-zone-text-<?php echo esc_attr( $block_id ); ?>');
            dropZoneText.style.display = 'block';

            // Reset the rating (if using radio buttons or select)
            resetRating();
        } else {
            alert('Error submitting testimonial: ' + (data.data.message || 'Unknown error.'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred.');
    });
}

function resetRating() {
    // Reset the hidden input value
    const ratingInput = document.getElementById('<?php echo esc_js( $block_id . "-rating-input" ); ?>');
    ratingInput.value = 0; // Reset to 0 or your default value

    // Reset the star colors
    const stars = document.querySelectorAll('#<?php echo esc_js( $block_id . "-rating" ); ?> .star');
    stars.forEach(star => {
        star.style.color = '#DDD'; // Reset the color to gray
    });
}
    </script>
    <?php
    return ob_get_clean();
}
?>
