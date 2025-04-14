import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

// Register GSAP plugins
gsap.registerPlugin(ScrollTrigger);

const animations = {
    init() {
        this.initHeaderAnimation();
        this.initScrollAnimations();
    },
    
    // Header animation
    initHeaderAnimation() {
        gsap.from('header', { 
            opacity: 0, 
            y: -20, 
            duration: 0.8,
            ease: 'power2.out'
        });
    },
    
    // Scroll-triggered animations
    initScrollAnimations() {
        // Fade in animations
        gsap.utils.toArray('.fade-in').forEach(element => {
            gsap.from(element, {
                opacity: 0,
                y: 20,
                duration: 1,
                scrollTrigger: {
                    trigger: element,
                    start: 'top 80%',
                    toggleActions: 'play none none none'
                }
            });
        });
        
        // Slide up animations
        gsap.utils.toArray('.slide-up').forEach(element => {
            gsap.from(element, {
                opacity: 0,
                y: 50,
                duration: 1,
                scrollTrigger: {
                    trigger: element,
                    start: 'top 80%',
                    toggleActions: 'play none none none'
                }
            });
        });
        
        // Slide in left animations
        gsap.utils.toArray('.slide-in-left').forEach(element => {
            gsap.from(element, {
                opacity: 0,
                x: -50,
                duration: 1,
                scrollTrigger: {
                    trigger: element,
                    start: 'top 80%',
                    toggleActions: 'play none none none'
                }
            });
        });
        
        // Slide in right animations
        gsap.utils.toArray('.slide-in-right').forEach(element => {
            gsap.from(element, {
                opacity: 0,
                x: 50,
                duration: 1,
                scrollTrigger: {
                    trigger: element,
                    start: 'top 80%',
                    toggleActions: 'play none none none'
                }
            });
        });
    }
};

export default animations;
