<?
function print_r_tree($data, $level = 0, $parent = '')
{
	global $USER;
    $out = '';
    if ($level == 0) {
        $out .='<script  data-skip-moving="true">function showinner(el) {if(el.nextSibling.style.display == "block") {el.nextSibling.style.display = "none";} else {el.nextSibling.style.display = "block";};}; </script>';
    }
    $type = gettype($data);
    if ($type == 'array') {
        if ($level > 0) {
            $out .= '<div style="display: none; padding: 5px; border: 1px solid #000;">';
        };
        foreach ($data as $key=>$val) {
            $item = '["'.$key. '"]';
            $valtype = gettype($val);
            if ($valtype == 'array') {
                $count = '('.count($val).')';
            } else if (in_array($valtype , array('integer', 'double', 'float', 'string')) ){
                $count = '('.strlen($val).')';
            };
            $out .= '<div><div onclick="showinner(this);" style="padding: 5px;">'.gettype($val). ' '.$parent.$item.' &nbsp; &nbsp; '.$count.'</div>';

            $out .= print_r_tree($val, $level + 1, $parent.$item);

            $out .= '</div>';
        };
        if ($level > 0) {
            $out .= '</div>';
        };
    } else if ($type == 'boolean') {
        if ($data) {
            $out .= '<pre style="display: none;  padding: 5px; border: 1px solid #000;">TRUE</pre>';
        } else {
            $out .= '<pre style="display: none;  padding: 5px; border: 1px solid #000;">FALSE</pre>';
        }
    } else if (in_array($type , array('integer', 'double', 'float', 'string')) ){
        $out .= '<pre style="display: none;  padding: 5px; border: 1px solid #000;">'.str_replace('<' , '&lt;', $data).'</pre>';
    } else {
        $out .= '<pre style="display: none;  padding: 5px; border: 1px solid #000;">'.print_r($data, true).'</pre>';
    };
    if ($level == 0) {
		if (isset($USER) && $USER->IsAdmin()) {
			echo $out;
		} else {
			echo '<div data-id="!!!----------DEBUG----------!!!" style="dislay: none">';
			echo $out;
			echo '</div>';
		}
    };
    return $out;
};