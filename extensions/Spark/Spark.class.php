<?php

/**
 * Class to render spark tags.
 * 
 * @since 0.1
 * 
 * @file Spark.class.php
 * @ingroup Spark
 * 
 * @licence GNU GPL v3+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
final class SparkTag {
	
	/**
	 * List of spark parameters.
	 * 
	 * @since 0.1
	 * 
	 * @var array
	 */
	protected $parameters;
	
	/**
	 * Constructor.
	 * 
	 * @since 0.1
	 * 
	 * @var string or null
	 */
	protected $contents;
	
	public function __construct( array $args, $contents ) {
		$this->parameters = $this->getSparkParameters( $args );
		$this->contents = $contents;
	}
	
	/**
	 * Renrder the spark div.
	 * 
	 * @since 0.1
	 * 
	 * @return string
	 */
	public function render() {
		return Html::element(
			'div',
			array_merge( array( 'class' => 'spark' ), $this->parameters ),
			$this->contents
		);
	}
	
	/**
	 * Get the spark parameters from a list of key value pairs.
	 * 
	 * @since 0.1
	 * 
	 * @param array $args
	 * 
	 * @return array
	 */
	protected function getSparkParameters( array $args ) {
		$parameters = array();
		
		foreach ( $args as $name => $value ) {
			if ( strpos( $name, 'data-spark-' ) === 0 ) {
				$parameters[$name] = $value;
			}
		}
		
		return $parameters;
	}
	
}