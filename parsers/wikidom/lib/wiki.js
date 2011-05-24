/*
 * Wikitext document object models
 * 
 * document
 *   blocks: Array
 *   attributes: Plain object
 * 
 * // Blocks
 * 
 * comment
 *   text: String
 * horizontal-rule
 * heading
 *   level: Integer (1..6)
 *   line: Line object
 * paragraph
 *   lines: Array of line objects
 * list
 *   style: String ("bullet" or "number")
 *   items: Array of item objects
 * table
 *   rows: Array of arrays of cell objects
 *   attributes: Plain object
 * 
 * // Components
 * 
 * line
 *   text: String
 * item
 *   line: Line object
 *   lists: Array of list objects
 * range
 *   offset: Integer
 *   length: Integer
 * annotation
 *   type: String
 *   range: Range object
 *   data: Plain object
 * cell
 *   document: Object
 *   attributes: Plain object
 */

// Global object - other modules will attach to this
var wiki = {};
