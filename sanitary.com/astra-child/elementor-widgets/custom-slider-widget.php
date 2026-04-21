<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit;

class Hero_Slider_Widget extends Widget_Base {

    public function get_name() { return 'hero_slider'; }
    public function get_title() { return 'Hero Slider'; }
    public function get_icon() { return 'eicon-slides'; }
    public function get_categories() { return ['general']; }

    protected function _register_controls() {

        $this->start_controls_section('slider_content',['label'=>'Slider Content']);

        $repeater = new \Elementor\Repeater();

        $repeater->add_control('background_image',[
            'label'=>'Background Image',
            'type'=>Controls_Manager::MEDIA,
            'default'=>['url'=>\Elementor\Utils::get_placeholder_image_src()],
        ]);

        $repeater->add_control('slide_h4',[
            'label'=>'H4 Text',
            'type'=>Controls_Manager::TEXT,
            'default'=>'H4 Heading',
        ]);

        $repeater->add_control('slide_h2',[
            'label'=>'H2 Text',
            'type'=>Controls_Manager::TEXT,
            'default'=>'H2 Heading',
        ]);

        $repeater->add_control('slide_p',[
            'label'=>'Paragraph',
            'type'=>Controls_Manager::TEXTAREA,
            'default'=>'Sample paragraph for slider.',
        ]);

        $repeater->add_control('button_1_text',['label'=>'Button 1 Text','type'=>Controls_Manager::TEXT,'default'=>'Button 1']);
        $repeater->add_control('button_1_link',['label'=>'Button 1 Link','type'=>Controls_Manager::URL]);
        $repeater->add_control('button_2_text',['label'=>'Button 2 Text','type'=>Controls_Manager::TEXT,'default'=>'Button 2']);
        $repeater->add_control('button_2_link',['label'=>'Button 2 Link','type'=>Controls_Manager::URL]);

        $this->add_control('slides',[
            'label'=>'Slides',
            'type'=>Controls_Manager::REPEATER,
            'fields'=>$repeater->get_controls(),
            'default'=>[
                ['slide_h4'=>'H4 1','slide_h2'=>'H2 1','slide_p'=>'Paragraph 1'],
                ['slide_h4'=>'H4 2','slide_h2'=>'H2 2','slide_p'=>'Paragraph 2'],
            ],
            'title_field'=>'{{{ slide_h2 }}}',
        ]);

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        ?>

        <div class="hero-swiper swiper-container">
            <div class="swiper-wrapper">
                <?php foreach($settings['slides'] as $slide): ?>
                    <div class="swiper-slide">
                        <div class="slide-bg" style="background-image: url('<?php echo esc_url($slide['background_image']['url']); ?>');"></div>
                        <div class="slide-content">
                            <h4><?php echo esc_html($slide['slide_h4']); ?></h4>
                            <h2><?php echo esc_html($slide['slide_h2']); ?></h2>
                            <p><?php echo esc_html($slide['slide_p']); ?></p>
                            <a class="btn btn1" href="<?php echo esc_url($slide['button_1_link']['url']); ?>"><?php echo esc_html($slide['button_1_text']); ?></a>
                            
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <!-- Navigation & Pagination -->
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-pagination"></div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var heroSwiper = new Swiper('.hero-swiper', {
                slidesPerView: 1,
                loop: true,
                speed: 1200,
                autoplay: { delay: 5000, disableOnInteraction: false },
                effect: 'fade',
                fadeEffect: { crossFade: true },
                navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
                pagination: { el: '.swiper-pagination', clickable: true },
                on: {
                    slideChangeTransitionStart: function () {
                        document.querySelectorAll('.slide-content > *').forEach(el => {
                            el.style.opacity = "0";
                            el.style.transform = "translateY(40px)";
                        });
                    },
                    slideChangeTransitionEnd: function () {
                        let activeSlide = document.querySelector('.swiper-slide-active .slide-content');
                        if (activeSlide) {
                            let items = activeSlide.children;
                            [...items].forEach((el, index) => {
                                el.style.transition = "all 0.8s ease";
                                el.style.transitionDelay = (index * 0.2) + "s";
                                el.style.opacity = "1";
                                el.style.transform = "translateY(0)";
                            });
                        }
                    }
                }
            });
        });
        </script>

        <style>
        .hero-swiper { width:100%; height:700px; position:relative; overflow:hidden; }
        .hero-swiper .swiper-slide {
            display:flex;
            justify-content:center;
            align-items:center;
            position:relative;
        }

        /* Background image container (for zoom animation) */
        .hero-swiper .slide-bg {
            position:absolute;
            top:0; left:0;
            width:100%; height:100%;
            background-size:cover;
            background-position:center;
/*             transform: scale(1.1); */
            transition: transform 3s ease;
        }
        .hero-swiper .swiper-slide-active .slide-bg {
            transform: scale(1); /* Zoom in-out effect */
        }

        /* Dark Overlay */
/*         .hero-swiper .slide-bg::after {
            content:"";
            position:absolute;
            top:0; left:0;
            width:100%; height:100%;
            background: rgba(0,0,0,0.45);
        } */

        .hero-swiper .slide-content {
            position: relative;
            z-index: 2;
            text-align: center;
            max-width: 750px;
            color:#fff;
            padding:20px;
        }

        .hero-swiper h4 {
            font-size: 12px;
            margin-bottom: 12px;
            font-weight: 400;
            color: #000;
            letter-spacing: 3px;
            text-transform: uppercase;
        }
        .hero-swiper h2 {
            margin-bottom: 18px;
            font-family: "DM Serif Text", Sans-serif;
            font-size: 65px;
            font-weight: 700;
            line-height: 1.2;
            color: #000;
        }
        .hero-swiper p {
            font-size: 18px;
            margin-bottom: 30px;
            color: #484848;
        }

        /* Buttons */
        .hero-swiper .btn {
            display:inline-block;
            margin:5px;
            padding:14px 35px;
            border-radius:30px;
            background:#000;
            color:#fff;
            text-decoration:none !important;
            font-weight:600;
            transition: all 0.2s ease;
        }
			.btn.btn2 {
				background: #000;
			}
			
        .hero-swiper .btn:hover { background:#000; transform:translateY(-3px); }
			
			.btn.btn1:hover {
				background: #000 !important;
			}
			.btn.btn2:hover {
				background: #000 !important;
			}

        /* Animation base for texts */
        .hero-swiper .slide-content > * {
            opacity: 0;
            transform: translateY(40px);
        }
        .hero-swiper .swiper-slide-active .slide-content > * {
            opacity: 1;
            transform: translateY(0);
        }
		.swiper-button-next::after {
			font-size: 26px !important;
			padding: 10px;
			border-radius: 4px;
			background: #ededed;
		}
		
		.swiper-button-prev::after {
			font-size: 26px !important;
			padding: 10px;
			border-radius: 4px;
			background: #ededed;
		}
		.hero-swiper .swiper-pagination-bullet {
        	width: 12px;
        	height: 12px;
        	background: #1f5d87;
        	opacity: 1;
        }
		.hero-swiper .swiper-pagination-bullet.swiper-pagination-bullet-active {
        	width: 60px;
        	height: 10px;
        	border-radius: 4px;
        	background: #000;
        }
		
		
		 @media only screen and (min-width:768px) and (max-width: 1024px) {
		.hero-swiper {height: 450px;}
		.hero-swiper h4 {font-size: 12px; }
        .hero-swiper h2 {font-size: 50px;}
        .hero-swiper p {width: 530px;margin: 0 auto;padding-bottom: 20px;}
		
		     
		 }
		 
		@media only screen and (max-width: 767px) {
		.hero-swiper {height: 250px;}
		.hero-swiper h4 {font-size: 12px; margin-bottom: 0px;}
        .hero-swiper h2 {font-size: 24px;}
        .hero-swiper p {font-size:12px}
		.hero-swiper .btn {padding: 10px 35px;}
		
		    
		}
			
			
			
        </style>

        <?php
    }

    protected function _content_template() {}
}

Plugin::instance()->widgets_manager->register_widget_type( new Hero_Slider_Widget() );
