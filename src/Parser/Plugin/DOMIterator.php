<?php

class Kint_Parser_Plugin_DOMIterator extends Kint_Parser_Plugin
{
    public function parse(&$var, Kint_Object &$o)
    {
        if (!is_object($var) || !(($var instanceof DOMNamedNodeMap) || ($var instanceof DOMNodeList))) {
            return;
        }

        // Recursion (This should never happen, should always be stopped at the parent DOMNode)
        if (in_array('recursion', $o->hints)) {
            return;
        }

        $o->size = $var->length;
        if ($o->size === 0) {
            $o->replaceRepresentation(new Kint_Object_Representation('Iterator'));
            $o->size = null;

            return;
        }

        // Depth limit
        // Make empty iterator representation since we need it in DOMNode to point out depth limits
        if ($this->parser->max_depth && $o->depth + 1 >= $this->parser->max_depth) {
            $b = new Kint_Object();
            $b->name = $o->classname.' Iterator Contents';
            $b->access_path = 'iterator_to_array('.$o->access_path.', true)';
            $b->depth = $o->depth + 1;
            $b->hints[] = 'depth_limit';

            $r = new Kint_Object_Representation('Iterator');
            $r->contents = array($b);
            $o->replaceRepresentation($r, 0);

            return;
        }

        $data = iterator_to_array($var, true);

        $r = new Kint_Object_Representation('Iterator');
        $o->replaceRepresentation($r, 0);

        foreach ($data as $key => $item) {
            $base_obj = new Kint_Object();
            $base_obj->depth = $o->depth + 1;
            $base_obj->name = $item->nodeName;

            if ($o->access_path) {
                if ($var instanceof DOMNamedNodeMap) {
                    $base_obj->access_path = $o->access_path.'->getNamedItem('.var_export($key, true).')';
                } elseif ($var instanceof DOMNodeList) {
                    $base_obj->access_path = $o->access_path.'->item('.var_export($key, true).')';
                } else {
                    $base_obj->access_path = 'iterator_to_array('.$o->access_path.', true)['.var_export($key, true).']';
                }
            }

            $r->contents[] = $this->parser->parse($item, $base_obj);
        }
    }
}