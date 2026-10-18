<?php
$Styles = array();
$Widgets__list = array();
#
$YC__WidgetsMachine = new YC__WidgetsMachine;
# INTRO SETUP
	$ShowIntro = false;
	$HomeIntro = ( is_array( get_option('HomeIntro') ) ) ? get_option('HomeIntro') : array();
	$HomeIntro = ( is_array( $HomeIntro ) ) ? $HomeIntro : array();
	if ( function_exists( 'kayan_i18n_localize_tree' ) ) {
		$HomeIntro = kayan_i18n_localize_tree( $HomeIntro );
	}
	#
	if( isset( $HomeIntro['SelectedModel'] ) && !empty( $HomeIntro['SelectedModel'] ) ){ $ShowIntro = true;
		$ActiveModel = $HomeIntro['SelectedModel'];

		$Intro_Data = ( ( isset( $HomeIntro[ $ActiveModel ] ) ) ) ? $HomeIntro[ $ActiveModel ] : array();
		#
		if( isset( $Intro_Data[ 'TextAreaColor' ] ) && strpos($Intro_Data[ 'TextAreaColor' ], PHP_EOL ) !== FALSE ){
			$Intro_Data[ 'TextAreaColor' ] = explode(PHP_EOL, $Intro_Data[ 'TextAreaColor' ]);
		}else{
			$Intro_Data[ 'TextAreaColor' ] = array();	
		}
		$HeaderAttr = 'style="'.implode('', $Intro_Data[ 'TextAreaColor' ]).'"';
		$Intro_Data['AttrStyle'] = $HeaderAttr;
		#
		$Path_style__Intro = $this->StylesPath.'YourColor__Intro';
		$StyleFields__Intro = array();
		#
		foreach( glob($Path_style__Intro.'/*.css') as $cs__file ) {
			$current_file_name = explode('YourColor__Intro/', $cs__file)[1];
			$current_file_name = explode('/', $current_file_name)[0];
			$current_file_name = explode('.css', $current_file_name)[0];
			$StyleFields__Intro[] = $current_file_name;
		}

		# Style FILES.
		if( in_array( $ActiveModel ,$StyleFields__Intro ) ){
			$Styles[$ActiveModel] = 'YourColor__Intro/'.$ActiveModel.'.css';
		}
	}

# HOME WIDGETS SETUP 
	$home_widgets = ( is_array( get_option( 'widgets_home__meta' ) ) ) ? get_option( 'widgets_home__meta' ) : array();
	$home_widgets = ( is_array( $home_widgets ) ) ? $home_widgets : array();
	if ( function_exists( 'kayan_i18n_localize_tree' ) ) {
		$home_widgets = kayan_i18n_localize_tree( $home_widgets );
	}

	if( !empty( $home_widgets ) ){
		$widgets__Enqueues = $YC__WidgetsMachine->widgets__Enqueues($home_widgets);
		$Styles = array_merge($Styles,$widgets__Enqueues);

		$extraxt__Widgetlist = $YC__WidgetsMachine->get__widgets__list($home_widgets);
		$Widgets__list = array_merge($Widgets__list,$extraxt__Widgetlist);
	}				


$kymeet_mode = ( function_exists( 'kymeet_store_active' ) && kymeet_store_active() );

$this->Part('header',array('Styles'=>$Styles,'IntroPage'=>true,'Widgets__list'=>$Widgets__list));

	# INTRO UI.
	if( $ShowIntro == true && ! $kymeet_mode ) $YC__WidgetsMachine->widgets__model__UI( array( 'model__value'=> $HomeIntro) );

	# WIDGETS UI
	if( $kymeet_mode ){
		$has_kymeet = false;
		$home_widgets = function_exists( 'kymeet_store_clean_widget_list' ) ? kymeet_store_clean_widget_list( $home_widgets ) : $home_widgets;
		foreach ( $home_widgets as $hw ) { if ( isset( $hw['widget_id'] ) && strpos( $hw['widget_id'], 'kymeet_' ) === 0 ) { $has_kymeet = true; break; } }
		if ( $has_kymeet ) { $YC__WidgetsMachine->widgets___UI( array('Widgets_data'=>$home_widgets,'WidgetID'=>'home_widgets') ); }
		else { kymeet_store_render_defaults(); }
	}elseif( !empty( $home_widgets ) )  $YC__WidgetsMachine->widgets___UI( array('Widgets_data'=>$home_widgets,'WidgetID'=>'home_widgets') );

$this->Part('footer',array('Styles'=>$Styles,'IntroPage'=>true,'Widgets__list'=>$Widgets__list));