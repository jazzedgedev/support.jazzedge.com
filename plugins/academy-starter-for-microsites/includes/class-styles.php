<?php
/**
 * Style-specific landing page content.
 *
 * @package Academy_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides SEO-focused content variations for Academy Starter microsites.
 */
class AcademyStarterStyles {
	/**
	 * Get style data by key.
	 *
	 * @param string $style_key Style/focus key.
	 * @return array<string, mixed>
	 */
	public static function get_style_data( $style_key ) {
		$styles = self::get_styles();
		$key    = sanitize_key( $style_key );

		if ( empty( $styles[ $key ] ) ) {
			$key = 'jazz_piano';
		}

		return $styles[ $key ];
	}

	/**
	 * Get all available style keys and labels.
	 *
	 * @return array<string, string>
	 */
	public static function get_style_options() {
		$options = array();

		foreach ( self::get_styles() as $key => $data ) {
			$options[ $key ] = $data['display_name'];
		}

		return $options;
	}

	/**
	 * Style definitions.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private static function get_styles() {
		static $styles = null;

		if ( null !== $styles ) {
			return $styles;
		}

		$styles = array(
			'cocktail'      => array(
				'display_name'            => 'Cocktail Piano',
				'adjective'               => 'cocktail',
				'keyword_phrases'         => array( 'cocktail piano', 'jazz cocktail piano', 'cocktail piano lessons', 'learn cocktail piano', 'lounge piano lessons' ),
				'hero_headline'           => 'Learn Cocktail Piano — Start Free Today',
				'hero_subheadline'        => 'Build the relaxed chord voicings, lounge-style textures, and tasteful fills that make cocktail piano sound polished. Academy Starter gives you a clear first path into elegant jazz cocktail piano playing.',
				'about_paragraph'         => array(
					'Cocktail piano is one of the most satisfying styles to develop because the results sound polished even at an early stage. The goal is to create a complete, full sound at the piano without needing a band behind you — just your two hands, a set of chord voicings, and a melody that feels relaxed and confident.',
					'What makes cocktail piano distinctive is its elegance. Where other styles lean on complexity or volume, cocktail piano rewards subtlety: the right chord placed at the right moment, a tasteful fill between phrases, a walking bass line that implies movement without demanding attention.',
					'Academy Starter builds the exact foundation this style requires. You will develop left hand independence, learn how shell voicings and guide tones create that unmistakable lounge sound, and begin connecting those skills to real songs.',
					'The 30-Day Playbook covers rhythm, technique, and chord vocabulary designed for adult learners who want results they can hear quickly. By the time you finish, you will understand how cocktail piano actually works — not just the notes, but the phrasing, the spacing, and the feel that makes a pianist sound like they belong in that room.',
				),
				'benefit_1'               => 'Create fuller cocktail piano textures with simple chord shapes.',
				'benefit_2'               => 'Learn a step-by-step routine for lounge-style practice.',
				'benefit_3'               => 'Start turning familiar songs into polished arrangements.',
				'testimonial_attribution' => 'Cocktail Piano Student',
				'seo_title'               => 'Cocktail Piano Lessons — Learn Jazz Cocktail Piano Free',
				'seo_description'         => 'Start free cocktail piano lessons today. Build jazz cocktail piano voicings, lounge textures, and tasteful fills with a structured 30-day program. No credit card required.',
				'focus_keyword'           => 'cocktail piano lessons',
				'og_description'          => 'Learn cocktail piano with a free structured 90-day program. Build jazz cocktail piano voicings, lounge textures, and tasteful fills. Start free today.',
				'seo_excerpt'             => 'Learn cocktail piano with a free structured 30-day program from JazzEdge Academy. Build jazz cocktail piano voicings, lounge-style textures, and tasteful fills. Start free — no credit card required.',
				'academy_search_url'      => 'https://jazzedge.academy/search/?q=cocktail+piano',
				'blog_search_url'         => 'https://jazzpianoblog.com/?s=cocktail+piano',
				'practice_topics'         => array(
					'Shell voicings and rootless left hand',
					'Guide tones and voice leading',
					'Lounge-style right hand fills and runs',
					'Walking bass lines for solo piano',
					'Reharmonizing familiar standards',
					'The cocktail pianist\'s chord vocabulary',
				),
				'unique_testimonial'      => '"I always wanted to play at dinner parties but never knew where to start. The cocktail piano approach clicked immediately — I learned more in 30 days than I had in years of random YouTube videos."',
				'unique_testimonial_name' => 'David R., Cocktail Piano Student',
				'style_intro'             => 'Cocktail piano lessons teach you to create a full, polished sound without needing a band. The style blends jazz harmony, elegant voicings, and tasteful phrasing into something that sounds sophisticated even at a beginner level. If you have ever heard a pianist playing softly in the background of a restaurant or hotel lobby and thought — I want to do that — cocktail piano is exactly what you are looking for. Academy Starter gives you the first structured path into that sound.',
				'style_faqs'              => array(
					array(
						'question' => 'Do I need to know jazz before starting cocktail piano lessons?',
						'answer'   => 'No. Academy Starter is designed for beginners and does not assume any jazz knowledge. Cocktail piano draws from jazz harmony, but you will learn the relevant concepts as part of the program. Many students come to cocktail piano with no jazz background at all and do very well.',
					),
					array(
						'question' => 'What kind of songs will I eventually play as a cocktail pianist?',
						'answer'   => 'Cocktail pianists typically play jazz standards, popular songs from the Great American Songbook, and familiar melodies arranged in a lounge style. Songs like Misty, Fly Me to the Moon, and The Way You Look Tonight are classic examples. Academy Starter builds the skills that make those arrangements possible.',
					),
					array(
						'question' => 'Can I learn cocktail piano without being able to read sheet music?',
						'answer'   => 'Yes. While the program introduces basic reading skills, cocktail piano is largely a chord-based style that relies on lead sheets and chord charts rather than fully notated sheet music. You will develop enough reading ability to use lead sheets, which is the standard format cocktail pianists work from.',
					),
				),
			),
			'funk'          => array(
				'display_name'            => 'Funk Piano',
				'adjective'               => 'funk',
				'keyword_phrases'         => array( 'funk piano', 'funk piano lessons', 'learn funk piano', 'funk keyboard grooves', 'rhythmic piano lessons' ),
				'hero_headline'           => 'Learn Funk Piano Grooves — Start Free Today',
				'hero_subheadline'        => 'Develop the rhythmic confidence, syncopation, and groove vocabulary that make funk keyboard parts feel alive. Academy Starter helps you build the foundation before layering in tighter funk piano ideas.',
				'about_paragraph'         => 'Funk piano is built on feel before it is built on notes. Most beginners try to learn funk by watching fast players and attempting to copy licks, which leads to frustration because the licks are not the point — the groove is the point. Funk keyboard playing is fundamentally rhythmic. Your chord stabs need to land in exactly the right place. Your left hand patterns need to lock with the kick and snare. Your right hand needs to know when to play and, just as importantly, when to stay silent. That level of rhythmic precision does not come from learning funk-specific material too early. It comes from building a rock-solid sense of timing, coordination, and musical awareness first. Academy Starter gives you that foundation. The 30-Day Playbook develops the rhythmic independence and chord confidence that makes funk patterns actually feel good when you play them. Students who rush into funk material without this foundation end up with parts that are technically correct but do not groove. Students who build the foundation first find that the funk-specific techniques click into place much faster and feel natural from the first time they try them. This is the right starting point for serious funk piano study.',
				'benefit_1'               => 'Strengthen your timing with focused funk piano practice.',
				'benefit_2'               => 'Build syncopated chord patterns that sit in the groove.',
				'benefit_3'               => 'Learn how simple ideas become confident keyboard parts.',
				'testimonial_attribution' => 'Funk Piano Student',
				'seo_title'               => 'Funk Piano Lessons — Learn Funk Piano Grooves Free',
				'seo_description'         => 'Start free funk piano lessons today. Build syncopation, rhythm, and funk piano grooves with a structured 30-day program. No credit card required.',
				'focus_keyword'           => 'funk piano lessons',
				'og_description'          => 'Learn funk piano grooves with a free 90-day program. Build syncopation, rhythm, and keyboard feel. Start your funk piano journey free today.',
				'seo_excerpt'             => 'Learn funk piano with a free structured 30-day program from JazzEdge Academy. Build syncopation, rhythm, and funk piano grooves step by step. Start free — no credit card required.',
				'academy_search_url'      => 'https://jazzedge.academy/search/?q=funk+piano',
				'blog_search_url'         => 'https://jazzpianoblog.com/?s=funk+piano',
				'practice_topics'         => array(
					'Syncopated chord stabs and rhythmic placement',
					'Groove-based left hand patterns',
					'The 16th-note feel and how to internalize it',
					'Funk keyboard riffs and repeated motifs',
					'Playing in the pocket — locking with the beat',
					'Building energy through rhythmic restraint',
				),
				'unique_testimonial'      => '"I play in a cover band and my keyboard parts always felt stiff. After working through the funk foundation material I finally understand what groove actually means at the keyboard."',
				'unique_testimonial_name' => 'Marcus T., Funk Piano Student',
				'style_intro'             => 'Funk piano is less about notes and more about feel. The goal is to lock into a rhythmic pocket, place your chords and stabs with precision, and leave space for the groove to breathe. Unlike classical or even jazz piano, funk keyboard playing rewards restraint — what you do not play is as important as what you do. Academy Starter builds the rhythmic and harmonic foundation you need before adding funk-specific techniques that actually sound right in a band context.',
				'style_faqs'              => array(
					array(
						'question' => 'Do I need a full 88-key keyboard to learn funk piano?',
						'answer'   => 'No. A 61-key keyboard is perfectly adequate for learning funk piano, and many professional keyboard players use 61 keys in live settings. The most important features are weighted or semi-weighted keys and a sustain pedal input. You do not need 88 keys to develop a great funk groove.',
					),
					array(
						'question' => 'Is funk piano hard to learn if I have a classical background?',
						'answer'   => 'Classical training gives you a strong technical foundation, but funk piano requires a significant rhythmic adjustment. Classical playing tends to emphasize evenness and precision in a metronomic sense, while funk playing requires you to feel behind the beat, place accents differently, and think about silence as much as sound. The transition takes focused work but is very achievable.',
					),
					array(
						'question' => 'What is the difference between funk piano and R&B piano?',
						'answer'   => 'Funk piano emphasizes rhythmic intensity, syncopation, and a raw, driving groove. R&B piano tends to be smoother, more melodic, and more focused on feel and emotion over rhythmic precision. The two styles overlap significantly and many of the foundational skills transfer between them. Academy Starter builds a foundation that supports both.',
					),
				),
			),
			'jazz_theory'   => array(
				'display_name'            => 'Jazz Theory',
				'adjective'               => 'jazz theory',
				'keyword_phrases'         => array( 'jazz theory', 'jazz harmony lessons', 'jazz chord theory', 'learn jazz theory', 'improvisation theory' ),
				'hero_headline'           => 'Learn Jazz Theory — Start Free Today',
				'hero_subheadline'        => 'Make jazz harmony easier to understand with a practical starter path through chords, progressions, rhythm, and improvisation concepts. Academy Starter turns jazz theory into something you can use at the piano.',
				'about_paragraph'         => 'Jazz theory has a reputation for being dense, academic, and disconnected from actual playing — and honestly, that reputation is earned when it is taught the wrong way. Most jazz theory resources present concepts in isolation: here is a scale, here is a chord, here is a mode. The problem is that none of it means anything until you hear it and feel it at the keyboard. Academy Starter takes the opposite approach. Every concept you encounter in this program is immediately connected to a sound and a physical action. You learn what a ii-V-I progression is by playing one. You understand tension and resolution by hearing what it feels like when a dominant chord resolves to a major chord. You develop ear training not through abstract interval drills but through the music itself. The 30-Day Playbook introduces rhythm, reading, chord construction, and basic harmonic movement in a sequence that builds on itself naturally. Students who complete this program report that jazz theory stops feeling like a separate subject and starts feeling like a language they are beginning to speak. That shift — from memorizing rules to actually understanding music — is exactly what Academy Starter is designed to create, and it is what separates this program from a theory textbook.',
				'benefit_1'               => 'Understand jazz chord theory through practical examples.',
				'benefit_2'               => 'Connect harmony, rhythm, and improvisation in one plan.',
				'benefit_3'               => 'Build confidence before tackling advanced jazz concepts.',
				'testimonial_attribution' => 'Jazz Theory Student',
				'seo_title'               => 'Jazz Theory Lessons — Learn Jazz Theory at the Piano Free',
				'seo_description'         => 'Start free jazz theory lessons today. Understand jazz harmony, chords, and improvisation with a structured 30-day piano program. No credit card required.',
				'focus_keyword'           => 'jazz theory lessons',
				'og_description'          => 'Learn jazz theory at the piano with a free structured 90-day program. Understand harmony, chords, and improvisation. Start free today.',
				'seo_excerpt'             => 'Learn jazz theory with a free structured 30-day program from JazzEdge Academy. Understand jazz harmony, chords, and improvisation directly at the keyboard. Start free — no credit card required.',
				'academy_search_url'      => 'https://jazzedge.academy/search/?q=jazz+theory',
				'blog_search_url'         => 'https://jazzpianoblog.com/?s=jazz+theory',
				'practice_topics'         => array(
					'Major, minor, and dominant chord construction',
					'The ii-V-I progression and why it matters',
					'Chord scales and how to choose the right one',
					'Interval recognition and ear training basics',
					'Reading lead sheets and chord charts',
					'How tension and resolution work in jazz harmony',
				),
				'unique_testimonial'      => '"Jazz theory always intimidated me because it seemed like a separate subject from playing. Academy Starter showed me how to connect the theory directly to the keyboard and it finally made sense."',
				'unique_testimonial_name' => 'Sophie L., Jazz Theory Student',
				'style_intro'             => 'Jazz theory becomes far less intimidating when you learn it at the piano rather than from a textbook. Understanding why chords move the way they do, how scales relate to harmony, and how improvisation is structured gives you a roadmap for playing jazz that feels creative rather than mechanical. Academy Starter connects every theory concept to a sound and a physical action at the keyboard so you are always learning music, not just memorizing rules.',
				'style_faqs'              => array(
					array(
						'question' => 'Do I need to play piano well before I start studying jazz theory?',
						'answer'   => 'No. Academy Starter is designed for beginners and introduces both the piano fundamentals and the theory concepts together. You do not need existing piano skills — the program builds them alongside the theory so that every concept you learn is immediately applied at the keyboard.',
					),
					array(
						'question' => 'How is jazz theory different from classical music theory?',
						'answer'   => 'Classical theory focuses heavily on written notation, voice leading rules, and formal harmonic analysis. Jazz theory is more practical and improvisational — it emphasizes chord symbols, lead sheets, the relationship between scales and chords, and how to make creative decisions in real time. Jazz theory is generally more immediately applicable to playing by ear and improvising.',
					),
					array(
						'question' => 'Will learning jazz theory actually help me improvise?',
						'answer'   => 'Yes — but not immediately. Theory gives you a map, but playing that map fluently takes practice. Understanding why certain notes work over certain chords, how tension and resolution function, and how chord progressions move will make your improvisation more intentional and musical over time. Academy Starter lays that foundation.',
					),
				),
			),
			'jazz_piano'    => array(
				'display_name'            => 'Jazz Piano',
				'adjective'               => 'jazz',
				'keyword_phrases'         => array( 'jazz piano', 'jazz piano lessons', 'learn jazz piano', 'jazz standards piano', 'jazz piano comping' ),
				'hero_headline'           => 'Learn Jazz Piano — Start Free Today',
				'hero_subheadline'        => 'Start building the technique, chords, rhythm, and confidence you need for jazz standards and everyday playing. Academy Starter gives you a guided first step into jazz piano lessons that feel approachable.',
				'about_paragraph'         => 'Learning jazz piano without a clear starting point is one of the most common ways to waste years of practice time. The style is wide — standards, bebop, blues, chord melody, comping, soloing, reharmonization — and most self-taught students spend years jumping between these areas without making real progress in any of them. The reason is not lack of effort. It is lack of foundation. Jazz piano requires a specific set of core skills: solid timing, two-hand independence, a working chord vocabulary, and the ability to listen and respond to what you are hearing. Without those four things in place, everything else in jazz becomes much harder than it needs to be. Academy Starter is designed to build exactly those four skills in a structured sequence that takes the guesswork out of practice. The 30-Day Playbook moves you through rhythm, technique, reading, and harmony in a way that has been tested with real students at every level. By the time you finish, you will have the foundation that makes jazz standards approachable, jazz chord voicings make sense, and the idea of improvising feel possible rather than terrifying. This is the starting point that serious jazz piano study requires.',
				'benefit_1'               => 'Build the core skills that support jazz standards.',
				'benefit_2'               => 'Practice chords, rhythm, and technique in a guided order.',
				'benefit_3'               => 'Prepare for comping, improvisation, and fuller arrangements.',
				'testimonial_attribution' => 'Jazz Piano Student',
				'seo_title'               => 'Jazz Piano Lessons — Learn Jazz Piano Free Today',
				'seo_description'         => 'Start free jazz piano lessons today. Build technique, chords, and confidence for jazz standards with a structured 30-day program. No credit card required.',
				'focus_keyword'           => 'jazz piano lessons',
				'og_description'          => 'Learn jazz piano with a free 90-day structured program. Build technique, chords, and confidence for jazz standards. Start your journey free.',
				'seo_excerpt'             => 'Learn jazz piano with a free structured 30-day program from JazzEdge Academy. Build technique, chords, and confidence for jazz standards and everyday playing. Start free — no credit card required.',
				'academy_search_url'      => 'https://jazzedge.academy/search/?q=jazz+piano',
				'blog_search_url'         => 'https://jazzpianoblog.com/?s=jazz+piano',
				'practice_topics'         => array(
					'Two-hand coordination for comping and melody',
					'Basic jazz chord voicings for the left hand',
					'Swing rhythm and how to feel the groove',
					'The anatomy of a jazz standard',
					'Simple improvisation over ii-V-I',
					'Developing a jazz practice routine that sticks',
				),
				'unique_testimonial'      => '"I had tried jazz piano twice before and quit both times because it felt impossible. This starter program gave me the smallest first steps and I actually kept going. Still going now."',
				'unique_testimonial_name' => 'Karen M., Jazz Piano Student',
				'style_intro'             => 'Jazz piano can feel impossibly wide when you are starting out. Standards, bebop, blues, chord voicings, improvisation, comping, soloing — the list never ends. The key is not to learn everything at once but to build a small set of real skills that actually transfer. Academy Starter focuses on exactly that: the technique, timing, and harmonic understanding that every jazz pianist needs before anything else. You will leave with a foundation that makes every jazz piano lesson after this one make more sense.',
				'style_faqs'              => array(
					array(
						'question' => 'How long does it realistically take to learn jazz piano?',
						'answer'   => 'Getting to a point where you can play jazz standards comfortably typically takes two to four years of consistent practice. That timeline shortens significantly when you start with a solid foundation rather than jumping straight into advanced material. Academy Starter is designed to compress the early stages of that journey so that every hour of practice after this builds on something real.',
					),
					array(
						'question' => 'Do I need to read sheet music to play jazz piano?',
						'answer'   => 'Not in the traditional sense. Jazz pianists primarily work from lead sheets, which show the melody and chord symbols rather than fully written-out arrangements. Academy Starter introduces enough reading to use lead sheets comfortably. Full classical notation reading is not required for jazz piano.',
					),
					array(
						'question' => 'What is the best first jazz standard to learn?',
						'answer'   => 'Autumn Leaves and Fly Me to the Moon are two of the most recommended starting points because they use common chord progressions in a clear, singable form. Academy Starter builds the chord vocabulary and rhythmic foundation that makes those songs approachable. The Super Simple Standards material included in Starter Plus introduces exactly this kind of entry-level standard.',
					),
				),
			),
			'blues_piano'   => array(
				'display_name'            => 'Blues Piano',
				'adjective'               => 'blues',
				'keyword_phrases'         => array( 'blues piano', 'blues piano lessons', 'learn blues piano', '12-bar blues piano', 'boogie woogie piano' ),
				'hero_headline'           => 'Learn Blues Piano — Start Free Today',
				'hero_subheadline'        => 'Get started with the rhythm, chord patterns, blues scales, and feel behind classic blues piano. Academy Starter helps you build the fundamentals that make 12-bar blues and boogie-woogie easier to play.',
				'about_paragraph'         => 'Blues piano is deeply rooted in feel, and feel is something that has to be developed — it cannot be faked. The shuffle rhythm, the bent phrases, the call and response between hands, the way a single note can hang in the air and say everything that needs to be said — all of that comes from a deep understanding of timing, touch, and the blues vocabulary. Many students try to learn blues piano by jumping straight into licks and 12-bar progressions, and while that approach can produce results, it often leaves gaps that show up later: stiff timing, mechanical phrasing, patterns that sound studied rather than felt. Academy Starter closes those gaps before they open. The 30-Day Playbook develops the rhythmic foundation, hand coordination, and musical intuition that blues piano demands. You will not just learn what to play — you will develop the internal sense of timing and phrasing that makes blues piano sound authentic. The boogie-woogie patterns, the blues scale, the 12-bar form, the slow blues feel — all of these become far more accessible when your foundation is solid. Students who complete the starter program and then move into blues-specific study consistently report that the style clicked in a way it never had before.',
				'benefit_1'               => 'Understand the foundation behind 12-bar blues piano.',
				'benefit_2'               => 'Build rhythm and coordination for blues accompaniment.',
				'benefit_3'               => 'Prepare for blues scales, licks, and boogie-woogie patterns.',
				'testimonial_attribution' => 'Blues Piano Student',
				'seo_title'               => 'Blues Piano Lessons — Learn Blues Piano Free Today',
				'seo_description'         => 'Start free blues piano lessons today. Master 12-bar blues, boogie-woogie, and blues scales with a structured 30-day program. No credit card required.',
				'focus_keyword'           => 'blues piano lessons',
				'og_description'          => 'Learn blues piano with a free 90-day program. Master 12-bar blues, boogie-woogie, and blues scales step by step. Start free today.',
				'seo_excerpt'             => 'Learn blues piano with a free structured 30-day program from JazzEdge Academy. Master 12-bar blues, boogie-woogie, and blues scales step by step. Start free — no credit card required.',
				'academy_search_url'      => 'https://jazzedge.academy/search/?q=blues+piano',
				'blog_search_url'         => 'https://jazzpianoblog.com/?s=blues+piano',
				'practice_topics'         => array(
					'The 12-bar blues form in all its variations',
					'Boogie-woogie left hand patterns',
					'The blues scale and how to use it musically',
					'Call and response phrasing for the right hand',
					'Shuffle rhythm and swing feel',
					'Slow blues and the art of bending notes on keys',
				),
				'unique_testimonial'      => '"I grew up listening to blues and always wanted to play it. The moment I got the shuffle rhythm locked in with my left hand I felt it — that was the day I knew I was actually playing blues piano."',
				'unique_testimonial_name' => 'James W., Blues Piano Student',
				'style_intro'             => 'Blues piano has a feeling that is instantly recognizable — that rolling left hand, those bent phrases, the way a single note can say so much. Getting there requires building real comfort with rhythm, the blues form, and the vocabulary of the style. Academy Starter gives you the foundational rhythm, timing, and harmonic skills that make blues piano possible. Once those are in place, the blues scale, boogie patterns, and 12-bar variations all start to connect in a way that feels natural rather than studied.',
				'style_faqs'              => array(
					array(
						'question' => 'Do I need to know music theory to play blues piano?',
						'answer'   => 'No. Blues piano is one of the most ear-friendly styles to learn because the forms are repetitive and the vocabulary is consistent. You will pick up relevant theory naturally as you learn — the 12-bar form, the blues scale, and the chord movement all make intuitive sense when you hear them. Academy Starter introduces just enough theory to support your playing without overwhelming you.',
					),
					array(
						'question' => 'What is the difference between blues piano and jazz piano?',
						'answer'   => 'Blues piano is more rooted in feel, repetition, and a specific set of rhythmic and melodic patterns. Jazz piano is harmonically more complex and improvisationally wider. Blues is often considered the foundation of jazz, and many jazz pianists started with blues. If you are deciding between them, blues is generally the more accessible starting point and the skills transfer directly into jazz later.',
					),
					array(
						'question' => 'Can I learn blues piano on a digital keyboard or does it need to be acoustic?',
						'answer'   => 'A digital keyboard works well for learning blues piano. Weighted keys are helpful because blues playing involves touch sensitivity — the way you press the keys affects the feel of the music. A keyboard with touch sensitivity and a sustain pedal is the minimum recommended setup. An acoustic piano is ideal but absolutely not required to make real progress.',
					),
				),
			),
			'music_theory'  => array(
				'display_name'            => 'Music Theory',
				'adjective'               => 'music theory',
				'keyword_phrases'         => array( 'music theory', 'music theory lessons', 'learn music theory', 'piano music theory', 'chord construction' ),
				'hero_headline'           => 'Learn Music Theory at the Piano — Start Free Today',
				'hero_subheadline'        => 'Make notes, rhythm, reading music, and chord construction easier to understand by learning them directly at the keyboard. Academy Starter gives you a practical route into music theory lessons that support real playing.',
				'about_paragraph'         => 'Music theory is one of those subjects that almost everyone wishes they understood better and almost no one learned properly the first time. If you took lessons as a child, theory was probably presented as a chore — scales to memorize, note names to drill, rules with no clear purpose. If you are self-taught, theory might feel like a foreign language you never had time to learn. Either way, the result is the same: you can play things, but you do not fully understand why they work, which limits how far you can go. Academy Starter changes that relationship. The program introduces music theory the way it should be learned — at the piano, connected to sound, with every concept immediately applied to something you can hear and play. You will learn how major and minor scales are built and why they feel different. You will understand how chords are constructed from intervals and how those chords create movement and emotion. You will develop the ability to read music not as a translation exercise but as a direct connection between the page and your hands. By the end of the 30-Day Playbook you will have a working theory vocabulary that supports everything else you want to do at the piano — whether that is playing songs by ear, reading sheet music, or understanding the music you love more deeply.',
				'benefit_1'               => 'Understand notes, rhythm, and chord construction more clearly.',
				'benefit_2'               => 'Connect music theory lessons to real piano practice.',
				'benefit_3'               => 'Build a foundation for reading, improvising, and learning songs.',
				'testimonial_attribution' => 'Music Theory Student',
				'seo_title'               => 'Music Theory Lessons — Learn Music Theory at the Piano Free',
				'seo_description'         => 'Start free music theory lessons at the piano today. Understand notes, chords, rhythm, and reading music with a structured 30-day program. No credit card required.',
				'focus_keyword'           => 'music theory piano lessons',
				'og_description'          => 'Learn music theory at the piano with a free 90-day program. Understand notes, chords, rhythm, and reading music. Start free today.',
				'seo_excerpt'             => 'Learn music theory at the piano with a free structured 30-day program from JazzEdge Academy. Understand notes, chords, rhythm, and reading music through practical keyboard exercises. Start free — no credit card required.',
				'academy_search_url'      => 'https://jazzedge.academy/search/?q=music+theory',
				'blog_search_url'         => 'https://jazzpianoblog.com/?s=music+theory',
				'practice_topics'         => array(
					'Reading notes on the treble and bass clef',
					'Rhythm values and how to count them at the piano',
					'Major and minor scales — how they are built',
					'Triads and seventh chords from any root',
					'Key signatures and the circle of fifths',
					'How chord progressions create movement and feeling',
				),
				'unique_testimonial'      => '"I always skipped theory and just tried to learn songs by ear. When I finally worked through the fundamentals I realized how much time I had wasted guessing at things I could have just understood."',
				'unique_testimonial_name' => 'Anna K., Music Theory Student',
				'style_intro'             => 'Music theory is not about memorizing rules — it is about understanding why music sounds the way it does. When you learn theory at the piano you hear every concept as you learn it, which makes it stick in a way that reading from a book never does. Academy Starter introduces the fundamentals in a logical order: rhythm first, then notes and scales, then chords and harmony. By the end you will have a working theory vocabulary that supports everything else you want to learn on the piano.',
				'style_faqs'              => array(
					array(
						'question' => 'Is it too late to learn music theory as an adult?',
						'answer'   => 'Not at all. Adults often learn music theory faster than children because they can understand abstract concepts more readily and connect them to music they already know and love. The main challenge for adults is finding a structured starting point that does not assume prior knowledge. Academy Starter is designed specifically for that situation.',
					),
					array(
						'question' => 'Do I need an actual piano to learn music theory?',
						'answer'   => 'A piano or keyboard is strongly recommended, though not strictly required for the theoretical concepts themselves. The reason Academy Starter teaches theory at the piano is that hearing and feeling every concept as you learn it is what makes the theory stick. A 61-key digital keyboard with touch sensitivity is more than enough to work through the entire program.',
					),
					array(
						'question' => 'How long does it take to understand the basics of music theory?',
						'answer'   => 'The foundational concepts — notes, rhythm, scales, and basic chords — can be understood in 30 to 60 days of consistent study. That is exactly what the Academy Starter 30-Day Playbook covers. Deeper theory like modes, extended harmony, and jazz chord progressions takes longer, but those topics build naturally on the foundation this program establishes.',
					),
				),
			),
			'rock_piano'    => array(
				'display_name'            => 'Rock Piano',
				'adjective'               => 'rock',
				'keyword_phrases'         => array( 'rock piano', 'rock piano lessons', 'learn rock piano', 'rock keyboard techniques', 'rock piano accompaniment' ),
				'hero_headline'           => 'Learn Rock Piano — Start Free Today',
				'hero_subheadline'        => 'Build the chords, rhythm, accompaniment patterns, and confidence behind strong rock piano parts. Academy Starter gives you the foundation for learning rock piano in a clear, song-friendly way.',
				'about_paragraph'         => 'Rock piano has its own identity that is distinct from every other keyboard style. It is not classical piano with distortion, and it is not jazz piano with a heavier beat. Rock keyboard playing has specific techniques, specific voicings, and a specific relationship with rhythm and the band that you have to understand before the parts start making sense. The power chord voicings, the driving octave bass lines, the way a rock piano fills space differently from a guitar — these are learnable skills, but they require a foundation in rhythm, coordination, and chord confidence that most beginners skip over. Academy Starter gives you that foundation in a structured 30-day sequence designed for adult learners who want to make real progress quickly. You will develop the timing precision that rock piano demands, the left hand strength and independence that makes driving bass patterns possible, and the chord vocabulary that lets you build parts that actually support a song rather than clutter it. Students who come to rock piano from a classical background often discover that the rhythmic emphasis requires more adjustment than they expected. Students who come with no background find that rock piano is an excellent entry point because the style rewards a solid groove over technical complexity. Both paths start here.',
				'benefit_1'               => 'Build stronger rhythm for rock piano accompaniment.',
				'benefit_2'               => 'Learn chord and keyboard patterns that support songs.',
				'benefit_3'               => 'Prepare for rock keyboard techniques with better fundamentals.',
				'testimonial_attribution' => 'Rock Piano Student',
				'seo_title'               => 'Rock Piano Lessons — Learn Rock Piano Free Today',
				'seo_description'         => 'Start free rock piano lessons today. Build chords, rhythm, and rock piano accompaniment with a structured 30-day program. No credit card required.',
				'focus_keyword'           => 'rock piano lessons',
				'og_description'          => 'Learn rock piano with a free 90-day program. Build chords, rhythm, and accompaniment for rock keyboard playing. Start free today.',
				'seo_excerpt'             => 'Learn rock piano with a free structured 30-day program from JazzEdge Academy. Build chords, rhythm, and rock piano accompaniment techniques step by step. Start free — no credit card required.',
				'academy_search_url'      => 'https://jazzedge.academy/search/?q=rock+piano',
				'blog_search_url'         => 'https://jazzpianoblog.com/?s=rock+piano',
				'practice_topics'         => array(
					'Power chords and how to voice them on piano',
					'Rock left hand patterns and octave bass lines',
					'Building and playing a rock groove',
					'Pentatonic scales for rock piano fills',
					'Playing with a band — listening and supporting the song',
					'The anatomy of a rock piano part',
				),
				'unique_testimonial'      => '"I started on guitar and switched to piano. Rock piano felt completely different but Academy Starter gave me a real framework. The power chord voicings alone changed how I approach the keyboard."',
				'unique_testimonial_name' => 'Tyler B., Rock Piano Student',
				'style_intro'             => 'Rock piano is not just classical piano played louder. The style has its own vocabulary — driving left hand patterns, power chord voicings, rhythmic energy, and fills that support the song without getting in the way. Whether you want to play in a band, write your own music, or just nail the keyboard parts of your favorite rock songs, you need a foundation built on rhythm, chord confidence, and the right technique. Academy Starter gives you that foundation before you dive into rock-specific material.',
				'style_faqs'              => array(
					array(
						'question' => 'Is rock piano easier to learn than classical piano?',
						'answer'   => 'They are different rather than one being easier. Rock piano requires less emphasis on reading full notation and more emphasis on rhythm, feel, and chord-based playing. Classical piano demands high technical precision and sight-reading ability. Most students find rock piano more immediately rewarding because you can play recognizable parts earlier in the learning process.',
					),
					array(
						'question' => 'What songs will I be able to play after completing the Starter program?',
						'answer'   => 'Academy Starter builds foundational skills rather than teaching specific rock songs. After completing the program you will have the chord knowledge, rhythm, and coordination to begin learning rock piano parts from songs you know. The Starter Plus blueprint lessons accelerate this by giving you specific techniques used in rock keyboard playing.',
					),
					array(
						'question' => 'Do I need a full 88-key keyboard for rock piano?',
						'answer'   => 'No. A 61-key keyboard is sufficient for learning rock piano and for most playing situations. Weighted or semi-weighted keys are recommended because rock piano involves dynamic playing — hitting keys with varying force to create energy and expression. A keyboard with velocity sensitivity and a sustain pedal covers everything you need for this program.',
					),
				),
			),
		);

		return $styles;
	}
}
