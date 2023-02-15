<?php

class Segment_Api_Js_Test extends WP_UnitTestCase {
	protected $object;

	public function setUp(): void {
		parent::setUp();
		$this->object = Segment_Api_Js::get_instance();
	}

	public function tearDown(): void {
		parent::tearDown();
	}

	public function test_segment_instance() {
		$class = new ReflectionClass( 'Segment_Api_Js' );

		$this->assertTrue( $class->hasProperty('instance') );
		$this->assertTrue( $class->getProperty('instance')->isStatic() );
	}

}

