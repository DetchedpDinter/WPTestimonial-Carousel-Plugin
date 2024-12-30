import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import { useBlockProps } from '@wordpress/block-editor';
import {
	TextControl,
	Button,
	TextareaControl,
	Notice,
} from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import './editor.scss';

const TestimonialFormEdit = ( props ) => {
	const { attributes, setAttributes } = props;
	const [ name, setName ] = useState( '' );
	const [ email, setEmail ] = useState( '' );
	const [ experience, setExperience ] = useState( '' );
	const [ rating, setRating ] = useState( 0 ); // Persistent rating
	const [ hoverRating, setHoverRating ] = useState( 0 ); // Hover effect
	const [ submitted, setSubmitted ] = useState( false );
	const [ error, setError ] = useState( '' );
	const [ image, setImage ] = useState( null );
	const [ dragOver, setDragOver ] = useState( false );
	const [ isSubmitting, setIsSubmitting ] = useState( false );
	const [ submissionError, setSubmissionError ] = useState( '' );

	const resetForm = () => {
		setName( '' );
		setEmail( '' );
		setExperience( '' );
		setRating( 0 );
		setImage( null );
	};

	const handleSubmit = async ( event ) => {
		event.preventDefault();

		if ( ! name || ! email || ! experience ) {
			setSubmissionError( 'Please fill in all required fields.' );
			return;
		}

		setIsSubmitting( true );

		try {
			// Make API request
			const response = await apiFetch( {
				path: '/testimonial-form/v1/submit',
				method: 'POST',
				data: {
					name,
					email,
					experience,
					rating,
					image_data: image,
				},
			} );

			if ( response.success ) {
				alert( 'Thank you for your testimonial!' );
				resetForm();
			} else {
				setSubmissionError( 'Submission failed. Please try again.' );
			}
		} catch ( error ) {
			console.error( 'Error during submission:', error );
			setSubmissionError( 'An error occurred. Please try again.' );
		} finally {
			setIsSubmitting( false );
		}
	};

	// Open File Dialog for Image Upload
	const openFileDialog = () => {
		const fileInput = document.createElement( 'input' );
		fileInput.type = 'file';
		fileInput.accept = 'image/*';
		fileInput.onchange = ( e ) => {
			const file = e.target.files[ 0 ];
			if ( file ) {
				const reader = new FileReader();
				reader.onloadend = () => setImage( reader.result );
				reader.readAsDataURL( file );
			}
		};
		fileInput.click();
	};

	// Drag and Drop functionality
	const handleDrop = ( e ) => {
		e.preventDefault();
		e.stopPropagation();
		//console.log('Dropped', e.dataTransfer.files[0]);
		const file = e.dataTransfer.files[ 0 ];
		if ( file ) {
			const reader = new FileReader();
			reader.onloadend = () => setImage( reader.result );
			reader.readAsDataURL( file );
		}
	};

	const handleDragOver = ( e ) => {
		e.preventDefault();
		e.stopPropagation();
	};

	return (
		<div { ...useBlockProps() }>
			{ error && <Notice status="error">{ error }</Notice> }

			{ submitted ? (
				<Notice status="success">
					{ __( 'Thank you for your feedback!' ) }
				</Notice>
			) : (
				<div className="testimonial-form">
					<TextControl
						value={ name }
						onChange={ ( value ) => setName( value ) }
						placeholder={ __( 'Enter Your Name' ) }
					/>
					<TextControl
						value={ email }
						onChange={ ( value ) => setEmail( value ) }
						placeholder={ __( 'Enter Your Email' ) }
					/>
					<TextareaControl
						value={ experience }
						onChange={ ( value ) => setExperience( value ) }
						placeholder={ __( 'Share Your Experience' ) }
					/>
					{ /* Star Rating System */ }
					<div
						className="rating-stars"
						style={ {
							display: 'flex',
							justifyContent: 'center',
							fontSize: '40px',
							cursor: 'pointer',
							marginBottom: '20px',
						} }
					>
						{ [ 1, 2, 3, 4, 5 ].map( ( star ) => (
							<span
								key={ star }
								onMouseEnter={ () => setHoverRating( star ) }
								onMouseLeave={ () => setHoverRating( 0 ) }
								onClick={ () => setRating( star ) }
								style={ {
									color:
										star <= ( hoverRating || rating )
											? '#FFD700'
											: '#DDD',
									transition: 'color 0.2s',
								} }
							>
								★
							</span>
						) ) }
					</div>
					{ /* Drag and Drop Image Upload */ }
					<div
						className="drag-and-drop-area"
						onDrop={ ( e ) => {
							handleDrop( e );
							setDragOver( false );
						} }
						onDragOver={ ( e ) => {
							handleDragOver( e );
							setDragOver( true );
						} }
						onDragLeave={ () => setDragOver( false ) }
						style={ {
							border: '2px dashed #ddd',
							borderRadius: '8px',
							padding: '10px',
							textAlign: 'center',
							marginBottom: '20px',
							position: 'relative',
							backgroundColor: dragOver ? '#eaf7e1' : '#fff',
							transition: 'background-color 0.3s ease',
							minHeight: '60px',
							display: 'flex',
							alignItems: 'center',
							justifyContent: 'center',
						} }
					>
						{ dragOver ? (
							<div
								className="drop-overlay"
								style={ {
									position: 'absolute',
									top: 0,
									left: 0,
									right: 0,
									bottom: 0,
									display: 'flex',
									alignItems: 'center',
									justifyContent: 'center',
									backgroundColor: 'rgba(0, 128, 0, 0.1)',
									borderRadius: '8px',
									fontSize: '14px',
									fontWeight: 'bold',
									color: '#006400',
								} }
							>
								Drop it here
							</div>
						) : image ? (
							<div className="image-preview">
								<img
									src={ image }
									alt="Uploaded Image"
									style={ {
										width: '100px', // Slightly bigger preview size
										height: '100px',
										objectFit: 'cover',
										borderRadius: '5px',
									} }
								/>
							</div>
						) : (
							<p style={ { margin: '0' } }>
								Drag and drop an image here.
							</p>
						) }
					</div>

					<div
						style={ {
							display: 'flex',
							justifyContent: 'space-between',
							marginTop: '20px',
						} }
					>
						<Button
							variant="secondary"
							onClick={ openFileDialog }
							className="add-picture-btn"
							style={ { width: '100%' } }
						>
							{ __( 'Add Picture' ) }
						</Button>
						<Button
							variant="primary"
							onClick={ handleSubmit }
							className="submit-btn"
							style={ { width: '100%' } }
						>
							{ __( 'Submit' ) }
						</Button>
					</div>
				</div>
			) }
		</div>
	);
};

export default TestimonialFormEdit;
