<?php 
$metaboxes = array(
	'title'    => ' الرئيسية ',
	'en_title'  => 'HOME OPTIONS',
	'icon'    => '<i class="fa-solid fa-house-chimney"></i>',
	'number'=>4,
	'disc'=>'اعدادات شكل الصفحة الرئيسية — الأقسام قابلة للترتيب والإخفاء والحذف من هنا',
	'fields'  => array(
		array(
			'id'=>'widgets_home__meta',
			'type'=>'Widgets',
			'title'=>'محتوي الصفحة الرئيسية ',
			'ModelCenter'=>'Standard',
			'update__type'=>'option',
		)
	)
);
