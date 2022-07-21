<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

CModule::IncludeModule("iblock");
    if(intval($_REQUEST["IBLOCK_ID_FIELDS"])>0){
        $bError = false;
        $IBLOCK_ID = intval($_REQUEST["IBLOCK_ID_FIELDS"]);
        $ib = new CIBlock;
        $arFields = CIBlock::GetArrayByID($IBLOCK_ID);
        $arFields["GROUP_ID"] = CIBlock::GetGroupPermissions($IBLOCK_ID);
        $arFields["NAME"] = $arFields["NAME"]."_new";
        unset($arFields["ID"]);
        if($_REQUEST["IBLOCK_TYPE_ID"]!="empty")
            $arFields["IBLOCK_TYPE_ID"]=$_REQUEST["IBLOCK_TYPE_ID"];
        $ID = $ib->Add($arFields);
            if(intval($ID)<=0)
                $bError = true;        
        if($_REQUEST["IBLOCK_ID_PROPS"]!="empty")
            $iblock_prop=intval($_REQUEST["IBLOCK_ID_PROPS"]);
        else
            $iblock_prop=$IBLOCK_ID;

        $iblock_prop_new = $ID;
        $ibp = new CIBlockProperty;
        $properties = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$iblock_prop));
        while ($prop_fields = $properties->GetNext()){
            if($prop_fields["PROPERTY_TYPE"] == "L"){
                $property_enums = CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"),
                                                               Array("IBLOCK_ID"=>$iblock_prop, "CODE"=>$prop_fields["CODE"]));
                while($enum_fields = $property_enums->GetNext()){
                    $prop_fields["VALUES"][] = Array(
                      "VALUE" => $enum_fields["VALUE"],
                      "DEF" => $enum_fields["DEF"],
                      "SORT" => $enum_fields["SORT"]
                    );
                }
            }
            $prop_fields["IBLOCK_ID"]=$iblock_prop_new;
            unset($prop_fields["ID"]);
            foreach($prop_fields as $k=>$v){
                if(!is_array($v))$prop_fields[$k]=trim($v);
                if($k{0}=='~') unset($prop_fields[$k]);
            }
            $PropID = $ibp->Add($prop_fields);
            if(intval($PropID)<=0)
                $bError = true;
        }
        if(!$bError && $IBLOCK_ID>0)
            LocalRedirect($APPLICATION->GetCurPageParam("success=Y",array("success","IBLOCK_ID_FIELDS")));
        else 
            LocalRedirect($APPLICATION->GetCurPageParam("error=Y",array("success","IBLOCK_ID_FIELDS")));
    }
    $str .='<form action='.$APPLICATION->GetCurPageParam().' method="post"><table>';    
    if($_REQUEST["success"]=="Y") $str .='<tr><td><font color="green">ИБ успешно скопирован</font>[b]</td></tr>';
    elseif($_REQUEST["error"]=="Y") $str .='<tr><td><font color="red">Произошла ошибка</font><br/></td></tr>';
    $str .='<tr><td>Копируем мета данные ИБ в новый ИБ[/b]<br/></td></tr>';
    $res = CIBlock::GetList(Array(),Array(),true);
        while($ar_res = $res->Fetch())
            $arRes[]=$ar_res;
    $str .='<tr><td>Копируем ИБ:<br><select name="IBLOCK_ID_FIELDS">';
    foreach($arRes as $vRes)    
        $str .= '<option value='.$vRes['ID'].'>'.$vRes['NAME'].' ['.$vRes["ID"].']</option>';
    $str .='</select></td>';
    $str .='<td>Копируем в новый ИБ свойства другого ИБ: *<br><select name="IBLOCK_ID_PROPS">';
    $str .='<option value="empty">';
    foreach($arRes as $vRes)    
        $str .= '<option value='.$vRes['ID'].'>'.$vRes['NAME'].' ['.$vRes["ID"].']</option>';
    $str .='</select></td></tr>';
    $str .='<tr><td>Копируем ИБ в тип:<br><select name="IBLOCK_TYPE_ID">';
    $str .='<option value="empty">';
    $db_iblock_type = CIBlockType::GetList();
    while($ar_iblock_type = $db_iblock_type->Fetch()){
       if($arIBType = CIBlockType::GetByIDLang($ar_iblock_type["ID"], LANG))
          $str .= '<option value='.$ar_iblock_type["ID"].'>'.htmlspecialcharsex($arIBType["NAME"])."</option>";
    }
    $str .='</select></td></tr>';
    $str .='<tr><td><br/>* если значение не указано мета данные ИБ секции "Свойства" берутся из ИБ первого поля</td></tr>';
    $str .='<tr><td><input type="submit" value="копируем"></td></tr>';
    $str .='</table></form>';    
    echo $str;
?>    