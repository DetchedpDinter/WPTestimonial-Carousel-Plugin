import React, { useEffect, useState } from 'react';
import { Fragment } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, ColorPalette } from '@wordpress/components';
import './editor.scss';

const TestimonialCarouselEdit = ( props ) => {
	const { attributes, setAttributes } = props;
	const {
		name,
		description,
		carouselGroup,
		carouselDesign,
		titleColor,
		contentColor,
		starColor,
		gradientColor1,
		gradientColor2,
		solidBgColor,
		glassOpacity,
		readMoreColor,
	} = attributes;
	const [ testimonials, setTestimonials ] = useState( [] );
	const [ currentIndex, setCurrentIndex ] = useState( 0 );
	const [ isExpanded, setIsExpanded ] = useState( false );
	const [ carouselSpeed, setCarouselSpeed ] = useState( 3000 ); // Default speed 3 seconds
	const [ isHovered, setIsHovered ] = useState( false );

	useEffect( () => {
		if ( ! carouselGroup ) {
			return; // Do nothing if no carousel group is selected
		}

		// Fetch testimonials from the custom API endpoint
		const fetchTestimonials = async () => {
			try {
				const response = await fetch(
					`/wp-json/custom/v1/testimonials?carousel_group=${ carouselGroup }`
				);
				const data = await response.json();
				console.log( 'Fetched Testimonials Data:', data ); // Check data structure
				setTestimonials( data );
			} catch ( error ) {
				console.error( 'Error fetching testimonials:', error );
			}
		};

		fetchTestimonials();
	}, [ carouselGroup ] ); // Re-fetch when the carousel group changes

	const carouselGroups = useSelect( ( select ) => {
		return select( 'core' ).getEntityRecords(
			'taxonomy',
			'carousel_group',
			{
				per_page: -1,
				_embed: true,
			}
		);
	}, [] );

	// Ensure the currentIndex is within bounds
	const totalTestimonials = testimonials.length;
	const currentTestimonial = testimonials[ currentIndex ];

	const nextTestimonial = () => {
		if ( totalTestimonials > 0 ) {
			setCurrentIndex(
				( prevIndex ) => ( prevIndex + 1 ) % totalTestimonials
			);
		}
	};

	const prevTestimonial = () => {
		if ( totalTestimonials > 0 ) {
			setCurrentIndex(
				( prevIndex ) =>
					( prevIndex - 1 + totalTestimonials ) % totalTestimonials
			);
		}
	};

	const defaultAvatarUrl =
		'https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp&f=y';

	const toggleContent = () => {
		setIsExpanded( ! isExpanded );
	};

	const renderStars = ( rating ) => {
		const maxStars = 5;
		return (
			<div className="testimonial-rating-container">
				{ Array.from( { length: maxStars }, ( _, i ) => (
					<span
						key={ i }
						className={ i < rating ? 'filled-star' : 'empty-star' }
						style={ { color: starColor } } // Apply selected star color
					>
						★
					</span>
				) ) }
			</div>
		);
	};

	// Automatically cycle through testimonials if not hovered
	useEffect( () => {
		if ( ! isHovered && totalTestimonials > 0 ) {
			const intervalId = setInterval( nextTestimonial, carouselSpeed );
			return () => clearInterval( intervalId ); // Cleanup the interval on component unmount or speed change
		}
	}, [ currentIndex, isHovered, carouselSpeed, totalTestimonials ] );

	// Determine the style based on selected design
	const getCarouselStyle = () => {
		switch ( carouselDesign ) {
			case 'gradient':
				return {
					background: `linear-gradient(to right, ${
						gradientColor1 || '#6a11cb'
					}, ${ gradientColor2 || '#2575fc' })`,
					borderRadius: '15px', // Rounded corners
				};
			case 'simple':
				return {
					borderRadius: '15px', // Rounded corners, no design
				};
			case 'solid':
				return {
					backgroundColor: solidBgColor || '#f3f4f6', // Default light grey
					borderRadius: '15px', // Rounded corners
					opacity: glassOpacity || 0.7, // Opacity for the glass effect
				};
			default:
				return {}; // No design
		}
	};

	const getTitleStyle = () => {
		return titleColor ? { color: titleColor } : {};
	};

	const getContentStyle = () => {
		return contentColor ? { color: contentColor } : {};
	};

	const getReadMoreStyle = () => {
		return readMoreColor ? { color: readMoreColor } : {}; // Apply the read more button color
	};

	return (
		<Fragment>
			<InspectorControls>
				<PanelBody title="Testimonial Settings">
					<SelectControl
						label="Select Carousel Group"
						value={ carouselGroup }
						options={ [
							{ label: 'Select', value: '' },
							...( carouselGroups
								? carouselGroups.map( ( group ) => ( {
										label: group.name,
										value: group.id,
								  } ) )
								: [] ),
						] }
						onChange={ ( value ) =>
							setAttributes( { carouselGroup: value } )
						}
					/>
					<SelectControl
						label="Select Carousel Design"
						value={ carouselDesign }
						options={ [
							{ label: 'Select', value: '' },
							{
								label: 'Gradient Background',
								value: 'gradient',
							},
							{
								label: 'Simple (No Design)',
								value: 'simple',
							},
							{
								label: 'Solid Background',
								value: 'solid',
							},
						] }
						onChange={ ( value ) =>
							setAttributes( { carouselDesign: value } )
						}
					/>
					{ carouselDesign === 'gradient' && (
						<div>
							<p>Select Gradient Colors:</p>
							<ColorPalette
								value={ gradientColor1 }
								onChange={ ( color ) =>
									setAttributes( {
										gradientColor1: color,
									} )
								}
							/>
							<ColorPalette
								value={ gradientColor2 }
								onChange={ ( color ) =>
									setAttributes( {
										gradientColor2: color,
									} )
								}
							/>
						</div>
					) }
					{ carouselDesign === 'solid' && (
						<div>
							<p>Select Solid Background Color:</p>
							<ColorPalette
								value={ solidBgColor }
								onChange={ ( color ) =>
									setAttributes( {
										solidBgColor: color,
									} )
								}
							/>
							<p>Select Glass Opacity:</p>
							<input
								type="range"
								min="0"
								max="1"
								step="0.1"
								value={ glassOpacity || 0.7 }
								onChange={ ( event ) =>
									setAttributes( {
										glassOpacity: event.target.value,
									} )
								}
							/>
						</div>
					) }
					<SelectControl
						label="Select Carousel Speed"
						value={ carouselSpeed }
						options={ [
							{ label: 'Select', value: '' },
							{ label: 'Fast (1s)', value: 1000 },
							{ label: 'Normal (3s)', value: 3000 },
							{ label: 'Slow (5s)', value: 5000 },
						] }
						onChange={ ( value ) =>
							setCarouselSpeed( Number( value ) )
						}
					/>
					<ColorPalette
						label="Title Text Color"
						value={ titleColor }
						onChange={ ( color ) =>
							setAttributes( { titleColor: color } )
						}
					/>
					<ColorPalette
						label="Content Text Color"
						value={ contentColor }
						onChange={ ( color ) =>
							setAttributes( { contentColor: color } )
						}
					/>
					<ColorPalette
						label="Star Color"
						value={ starColor }
						onChange={ ( color ) =>
							setAttributes( { starColor: color } )
						}
					/>
					<ColorPalette
						label="Read More Button Text Color"
						value={ readMoreColor }
						onChange={ ( color ) =>
							setAttributes( { readMoreColor: color } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...useBlockProps() }>
				<div
					className="testimonial-carousel-container"
					style={ getCarouselStyle() }
					onMouseEnter={ () => setIsHovered( true ) }
					onMouseLeave={ () => setIsHovered( false ) }
				>
					{ totalTestimonials === 0 ? (
						<p>No testimonials available.</p>
					) : (
						<div className="testimonial-card">
							<button
								className="arrow-button left-arrow"
								onClick={ prevTestimonial }
							>
								&lt;
							</button>
							<div className="testimonial-card-left">
								<img
									className="avatar"
									src={
										currentTestimonial?.featured_image ||
										defaultAvatarUrl
									}
									alt={ currentTestimonial?.title }
								/>
							</div>
							<div className="testimonial-card-right">
								<h4
									className="testimonial-name"
									style={ getTitleStyle() }
								>
									{ currentTestimonial?.title }
								</h4>
								{ renderStars( currentTestimonial?.rating ) }
								<div
									className={ `testimonial-description ${
										isExpanded ? 'expanded' : ''
									}` }
									dangerouslySetInnerHTML={ {
										__html: currentTestimonial?.content,
									} }
									style={ getContentStyle() }
								/>
								<button
									className="read-more"
									onClick={ toggleContent }
									style={ getReadMoreStyle() } // Apply the Read More button color
								>
									{ isExpanded ? 'Read Less' : 'Read More' }
								</button>
							</div>
							<button
								className="arrow-button right-arrow"
								onClick={ nextTestimonial }
							>
								&gt;
							</button>
						</div>
					) }
				</div>
			</div>
		</Fragment>
	);
};

export default TestimonialCarouselEdit;
