<!-- BEGIN: main -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>PSU Computer Store Work Orders</title>
<link href="templates/css/backend.css" rel="stylesheet" type="text/css" media="all" />
<script src="js/SpryValidationTextField.js" type="text/javascript"></script>
<link href="js/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
<script type="text/javascript">
<!--
function MM_callJS(jsStr) { //v2.0
  return eval(jsStr)
}
function calcMarkup(cost){
	if(cost=='')
		return '';
	cost=parseFloat(cost);
	if(cost*{markup}>{markup_max})
		custcost= cost+{markup_max};
	else
		custcost= cost*(1+{markup});
	if(custcost==0)
		return '0.00';
	else
	return CurrencyFormatted(Math.round(custcost*Math.pow(10,2))/Math.pow(10,2));

}
function CurrencyFormatted(amount)
{
	if(amount=='')
		return '';
	var i = parseFloat(amount);
	if(isNaN(i)) { i = 0.00; }
	var minus = '';
	if(i < 0) { minus = '-'; }
	i = Math.abs(i);
	i = parseInt((i + .005) * 100);
	i = i / 100;
	s = new String(i);
	if(s.indexOf('.') < 0) { s += '.00'; }
	if(s.indexOf('.') == (s.length - 2)) { s += '0'; }
	s = minus + s;
	return s;
}
function warrantyCheck(num, checked)
{
	cost = 'cost'+num;
	custcost = 'customercost'+num;
	if(checked == 1)
	{
		MM_setTextOfTextfield(cost,'','0.00');
		MM_setTextOfTextfield(custcost,'','0.00');
	}
	else
	{
		MM_setTextOfTextfield(cost,'','');
		MM_setTextOfTextfield(custcost,'','');
	}
}
function vendorCheck()
{
    var cnt = -1;
	var btn = document.partsform.vendor;
    for (var i=btn.length-1; i > -1; i--) {
        if (btn[i].checked) {
		cnt = i;
		i = -1;
			if(btn[cnt].value=="Other" && document.partsform.other.value==""){
				alert("You must specify the name of the vendor in the text field, if you select Other");
				return false;
			}
		}
    }
	if(document.partsform.item0.value==''){
		alert("You must specify at least one part to order");
		return false;
	}
	if(document.partsform.cost0.value==''||document.partsform.customercost0.value==''){
		alert("You must specify the cost of the part");
		return false;
	}
	if(document.partsform.item1.value!='' && (document.partsform.cost1.value==''||document.partsform.customercost1.value=='')){
		alert("You must specify the cost of the part");
		return false;
	}
	if(document.partsform.item2.value!='' && (document.partsform.cost2.value==''||document.partsform.customercost2.value=='')){
		alert("You must specify the cost of the part");
		return false;
	}
	if(document.partsform.item3.value!='' && (document.partsform.cost3.value==''||document.partsform.customercost3.value=='')){
		alert("You must specify the cost of the part");
		return false;
	}
	if(document.partsform.item4.value!='' && (document.partsform.cost4.value==''||document.partsform.customercost4.value=='')){
		alert("You must specify the cost of the part");
		return false;
	}
	if (cnt > -1) return btn[cnt].value;
    else {
		alert("You must choose a vendor for this part");
		return false;
	}
	
}
                  

function MM_setTextOfTextfield(objId,x,newText) { //v9.0
  with (document){ if (getElementById){
    var obj = getElementById(objId);} if (obj) obj.value = newText;
  }
}
//-->
</script>
</head>
<body>
<div align="center">
<!-- BEGIN: successmessage -->
	<span class="success">Part Request Succesfully Submitted For Processing</span>
<!-- END: successmessage -->
<form action="process_part_request.php" enctype="multipart/form-data" method="post" id="partsform" name="partsform" onsubmit="return vendorCheck();">
	<table width="100%" cellpadding="5" cellspacing="0">
    <tbody>
    	<tr >
        	<td colspan="4" align="left"><h5>Vendor</h5><input name="id" type="hidden" value="{id}" /></td>
        </tr>
        <tr>
        	<td width="15">&nbsp;</td>
            <td align="left">
            	<input name="vendor" type="radio" value="Apple" /><label>Apple</label><br />
                <input name="vendor" type="radio" value="Amazon" /><label>Amazon</label><br />
                <input name="vendor" type="radio" value="Crucial" /><label>Crucial</label><br />
                <input name="vendor" type="radio" value="Cyber Guys" /><label>Cyber Guys</label><br />
                <input name="vendor" type="radio" value="Dell" /><label>Dell</label>
                
            </td>
            <td align="left">
            	<input name="vendor" type="radio" value="Gov Connection" /><label>Gov Connection</label><br />
                <input name="vendor" type="radio" value="HP" /><label>HP</label><br />
                <input name="vendor" type="radio" value="Impact Computers" /><label>Impact Computers</label><br />
                <input name="vendor" type="radio" value="LCDs4Less"  /><label>LCDs4Less</label>
            </td>
            <td align="left" valign="top">
                <input name="vendor" type="radio" value="NewEgg"  /><label>NewEgg</label><br />
            	<input name="vendor" type="radio" value="Parts People" /><label>Parts People</label><br />
                <input name="vendor" type="radio" value="Stock" /><label>Stock</label><br />
                <input name="vendor" type="radio" value="Other" /><label>Other</label>&nbsp;&nbsp;<input name="other" type="text" size="25" maxlength="50" />
            </td>
        </tr>
        <tr >
        	<td colspan="4" align="left"><h5>Items To Order</h5></td>
        </tr>
        <tr>
        	<td>&nbsp;</td>
        	<td colspan="3">
            <table cellpadding="3" cellspacing="0" border="0" class="border" width="100%">
            <tbody>
       		 
        	<tr class="tablehead">
        		<td>Item</td>
            	<td align="center">Product #</td>
            	<td align="center">Quantity</td>
                <td align="center">Warranty</td>
            	<td align="center">Cost Each</td>
            	<td align="center">Customer Cost Each</td>
        	</tr>
            <!-- BEGIN: itemrow -->
            <tr {rowclass}>
        		<td><span id="item{num}"><input name="item{num}" type="text" size="50" maxlength="200" value=""/></span></td>
            	<td><input name="productnum{num}" type="text" size="15" maxlength="30" /></td>
            	<td><span id="quantitiyfield{num}">
                <input name="quantity{num}" type="text" value="1" size="5" maxlength="5" />
                </span></td>
                <td>
                	<input name="warranty{num}" type="checkbox" value="1" onpropertychange="warrantyCheck({num},document.partsform.warranty{num}.checked)" onblur="warrantyCheck({num},document.partsform.warranty{num}.checked)" />
                </td>
            	<td><span id="costfield{num}">
                <label>$</label><input name="cost{num}" type="text" id="cost{num}" onblur="MM_setTextOfTextfield('cost{num}','',CurrencyFormatted(document.partsform.cost{num}.value)); MM_setTextOfTextfield('customercost{num}','',calcMarkup(document.partsform.cost{num}.value))" size="5" />
                </span></td>
            	<td><span id="customercostfield{num}">
            	 <label>$</label> <input name="customercost{num}" type="text" id="customercost{num}" onchange="MM_setTextOfTextfield('customercost{num}','',CurrencyFormatted(document.partsform.customercost{num}.value))" size="5" />
            	 </span></td>
        	</tr>
        <!-- END: itemrow -->
        	</tbody>
            </table>
            </td>
      </tr>
      <tr >
        	<td colspan="4"><div align="center">
        	  <input name="submit" type="submit" value="Submit" />&nbsp;&nbsp;<input name="reset" type="reset" value="Reset" />
        	</div></td>
        </tr>
    </tbody>
    </table>
    </form>
    </div>
<script type="text/javascript">
<!--
<!-- BEGIN: validationcheck -->
var sprytextfield{num} = new Spry.Widget.ValidationTextField("quantitiyfield{num}", "integer", {isRequired:false, validateOn:["blur"]});
var sprytextfield{num} = new Spry.Widget.ValidationTextField("costfield{num}", "real", {validateOn:["blur"], isRequired:false});
var sprytextfield{num} = new Spry.Widget.ValidationTextField("customercostfield{num}", "real", {isRequired:false, validateOn:["blur"]});
<!-- END: validationcheck -->
//-->
</script>
</body>
</html>
<!-- END: main -->
