<?php

/**
 * A query printer for maps using the Open Layers API.
 *
 * @file SM_OpenLayersQP.php 
 * @ingroup SMOpenLayers
 *
 * @author Jeroen De Dauw
 */
class SMOpenLayersQP extends SMMapPrinter {

	/**
	 * @see SMMapPrinter::getServiceName
	 */		
	protected function getServiceName() {
		return 'openlayers';
	}	
	
	/**
	 * @see SMMapPrinter::addSpecificMapHTML
	 */
	public function addSpecificMapHTML() {
		return Html::element(
			'div',
			array(
				'id' => $this->service->getMapId(),
				'style' => "width: $this->width; height: $this->height; background-color: #cccccc; overflow: hidden;",
			),
			wfMsg( 'maps-loading-map' )
		);
	}

	/**
	 * Returns type info, descriptions and allowed values for this QP's parameters after adding the
	 * specific ones to the list.
	 * 
	 * @return array
	 */
    public function getParameters() {
        $params = parent::getParameters();
        
        $params[] = array( 'name' => 'controls', 'type' => 'enum-list', 'description' => wfMsg( 'semanticmaps_paramdesc_controls' ), 'values' => MapsOpenLayers::getControlNames() );
        $params[] = array( 'name' => 'layers', 'type' => 'enum-list', 'description' => wfMsg( 'semanticmaps_paramdesc_layers' ), 'values' => MapsOpenLayers::getLayerNames() );
        
        return $params;
    }
	
}