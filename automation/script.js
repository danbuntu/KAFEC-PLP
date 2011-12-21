currentItem = prj;
currentItemStack.push(prj);

// SCRIPT
currentItemStack.push(currentItem);


function PHPClassDefinition(pUMLClass) {

	var oUMLClass = pUMLClass;
	var oParentUMLClass = null;
	var vImplInterfacesArr=new Array();
	
	var vHasAbstractOperations=false;	

	var vAtrArr=__getAtributesArr();
	var vOprArr=__getMethodsArr(oUMLClass);
	
	this.getDefinition = function() {
	
		var vDefStr="";
		var vInterfaceMethods=__getImplInterfacesMethodsArr();
		var vAssocAtrArr=__getAssociationAtrArr();

		if(oUMLClass.IsLeaf) vDefStr+="final ";
		if(oUMLClass.IsAbstract||vHasAbstractOperations) vDefStr+="abstract ";
	
		vDefStr+="class " + oUMLClass.Name;	
		
		if(oParentUMLClass!=null) vDefStr+=" extends " + oParentUMLClass.Name;
		if(vImplInterfacesArr.length>0)
			vDefStr+=' implements '+__getImplInterfacesNamesStr();
		vDefStr+=" {\n";
		
		// wygenerowanie atrybutów
		for(var j=0;j<vAtrArr.length;j++) vDefStr+="\n\t"+vAtrArr[j]+"\n";
		// wygenerowanie atrybutów wynikaj¹cych z asocjacji
		for(var j=0;j<vAssocAtrArr.length;j++) vDefStr+="\n\t"+vAssocAtrArr[j];
		vDefStr+="\n";
		// wygenerowanie operacji
		for(var j=0;j<vOprArr.length;j++) vDefStr+="\n\t"+vOprArr[j]+"\n";	
		
		// operacje z implementowanych interfejsów		
		for(var j=0;j<vInterfaceMethods.length;j++) vDefStr+="\n\t"+vInterfaceMethods[j]+"\n";	

		vDefStr+="\n}\n";
	
		return(vDefStr);		
	};
	
	this.setParentClass = function(pUMLClass) {
		oParentUMLClass=pUMLClass;
		return;
	}

	this.addImplementedInterface = function(pUMLInterface) {
		vImplInterfacesArr.push(pUMLInterface);
		return;
	}
	
	// pobiera tablicê definicji artrybutów klasy
	function __getAtributesArr() {
		var vItemArr = new Array();	
		var vItemCount = oUMLClass.MOF_GetCollectionCount("Attributes");
		for(var j=0;j<vItemCount;j++) 
			vItemArr.push(__getAtributeDefStr(oUMLClass.MOF_GetCollectionItem("Attributes",j)));
		return(vItemArr);
	}
	
	// pobiera tablicê definicji atrubutów wynikaj¹cych z asocjacji 
	function __getAssociationAtrArr() {
		var vItemArr = new Array();	
		var vItemCount = oUMLClass.GetAssociationCount();
		var oItem=null;
		for(var j=0;j<vItemCount;j++) 
		{
			oItem=(oUMLClass.GetAssociationAt(j)).GetOtherSide(); //pobieram drugi koniec asocjacji
			if(oItem.IsNavigable) {
				vItemArr.push('// association with '+oItem.Participant.Name+' class');
				vItemArr.push(__getAtributeDefStr(oItem));
			}
			oItem=null;
		}		
		return(vItemArr);
	}


	// pobiera tablicê definicji metod klasy 
	function __getMethodsArr(oMethodContainer) {
		var vItemArr = new Array();
		var vItemCount = oMethodContainer.MOF_GetCollectionCount("Operations");
		for(var j=0;j<vItemCount;j++) 		
			vItemArr.push(__getMethodDefStr(oMethodContainer.MOF_GetCollectionItem("Operations",j)));	
		return(vItemArr);
	}
	
	// pobiera tablicê definicji metod z implementowanych interfejsów
	function __getImplInterfacesMethodsArr() {
		var vItemArr = new Array();
		for(var i=0;i<vImplInterfacesArr.length;i++)
			vItemArr=vItemArr.concat(__getMethodsArr(vImplInterfacesArr[i]));	
		return(vItemArr);
	}

	// pobiera tablicê definicji parametrów metody
	function __getMethodParamArr(oMethod) {
		var vItemArr = {'in' : null, 'return' : null};
		vItemArr['in'] = new Array();
		vItemArr['return'] = new Array();
		var vItemCount = oMethod.MOF_GetCollectionCount("Parameters");
		var oItem=null;
		var vDirection=null;
		for(var j=0;j<vItemCount;j++) 
		{
			oItem=oMethod.MOF_GetCollectionItem("Parameters",j); //pobieram parametr
			vDirection=oItem.MOF_GetAttribute("DirectionKind");
			if(vDirection!='pdkReturn') vItemArr['in'].push(__getMethodParamDefStr(oItem));
			else vItemArr['return'].push(__getMethodParamDefStr(oItem));
			oItem=null;
			vDirection=null;
		}				
		return(vItemArr);
	}
	

	// pobiera listê nazw interfejsów implementowanych w klasie
	function __getImplInterfacesNamesStr() {
		var vINamesArr= new Array();
		for(var i=0;i<vImplInterfacesArr.length;i++)
				vINamesArr.push(vImplInterfacesArr[i].Name);
		return(vINamesArr.join(','));
	}		

	// tworzy ci¹g znaków z definicj¹ metody klasy
	function __getMethodDefStr(oMethod)
	{
		var vStrFinal,vStrAbstract,vStrVis,vStrName,vInParamStr,vBodyStr,vIsAbstract,vParamArr;
		
		vStrFinal=(oMethod.MOF_GetAttribute("IsLeaf")=='True')?'final ':'';
		if(oMethod.MOF_GetAttribute("IsAbstract")=="True") {
			vStrAbstract='abstract ';			
			vIsAbstract=true; // aktualna metoda jest abstrakcyjna - nie moze mieæ cia³a
			// ustawiam flage, ¿e klasa ma co najmniej jednb¹ operacjê abstrakcyjn¹
			vHasAbstractOperations=true; 		
		}
		else {
			vStrAbstract='';
			vIsAbstract=false;			
		}
		vStrVis=__getVisibilityStr(oMethod.MOF_GetAttribute("Visibility"));
		vStrName=oMethod.MOF_GetAttribute("Name");			
		vParamArr=__getMethodParamArr(oMethod);
		// parametry nag³ówka operacji
		vInParamStr=vParamArr['in'].join(',');
		
		if(vIsAbstract) vBodyStr=';';   // jezeli jest abstrakcyjna to nie ma cia³a
		else // wstawiam cia³o funkcji
		{			
			vBodyStr=" {\n\n\t\t";
			if(vParamArr['return'].length>0) {
				vBodyStr+= "$" + vParamArr['return'][0] + "=NULL;\n\n\t\t";
				vBodyStr+= "return($" + vParamArr['return'][0] + ");\n\t}\n";
			}
			else vBodyStr+="return;\n\t}\n";
		}
		return(vStrFinal+vStrAbstract+vStrVis+' function '+vStrName+'('+vInParamStr+')'+vBodyStr);
	}

	// tworzy ci¹g znaków z definicj¹ atrybutu klasy
	function __getAtributeDefStr(oAtribute)
	{
		var vStrVis='',vStrName='',vStrMulti='';
		vStrVis=__getVisibilityStr(oAtribute.MOF_GetAttribute("Visibility"));
		vStrName="$" + oAtribute.MOF_GetAttribute("Name");		
		vStrMulti=(oAtribute.Multiplicity.indexOf('*',0)==-1)?'':'=array()'; // jezeli wielowartosciowy to robie tablice 		
		return(vStrVis+' '+vStrName+vStrMulti+';');
	}
	
	// tworzy ci¹g znaków z definicj¹ parametru metody
	function __getMethodParamDefStr(oParam) {
		
		var vDirection,vType='',vName='';		
		vDirection=oParam.MOF_GetAttribute("DirectionKind");		
		if(vDirection!='pdkReturn')
		{
		    vType=((oParam.TypeExpression.length>0)?(oParam.TypeExpression+' '):'');	
		    if( (vDirection=='pdkInout')||(vDirection=='pdkOut') ) vName+='&$';
		    else vName+='$'; // pdkIn				
		    vName+=oParam.MOF_GetAttribute("Name");
		}	
		else vName=oParam.MOF_GetAttribute("Name");
		return(vType+vName);
	}

	function __getVisibilityStr(vCode) {
		var vVis="";
		switch(vCode) {
			case 'vkPublic'    : { vVis="public"; break;}
			case 'vkProtected' : { vVis="protected"; break;}
			case 'vkPrivate'   : { vVis="private" ;break;} 
			default            : { vVis="public"; break;}
		}
		return(vVis);
	}

	function __getAtrChangeabilityStr(vCode) {
		// aktualnie nie uzywana do PHP
		var vCha="";
		switch(vCode) {
			case 'ckChangeable' : { vCha=""; break;}
			case 'ckFrozen'     : { vCha="final"; break;}
			case 'ckAddOnly'    : { vCha="" ;break;} 
			default             : { vCha=""; break;}
		}
		return(vCha);
	}	
}

function PHPInterfaceDefinition(pUMLInterface) {

	var oUMLInterface = pUMLInterface;
	var vHasAbstractOperations=false;	
	var vOprArr=__getMethodsArr(oUMLInterface);
	
	this.getDefinition = function() {
	
		var vDefStr="";
	
		vDefStr+="interface " + oUMLInterface.Name+" {\n";
	
		// wygenerowanie operacji
		for(var j=0;j<vOprArr.length;j++) vDefStr+="\n\t"+vOprArr[j]+"\n";	

		vDefStr+="\n}\n";
		return(vDefStr);		
	};
	

	// pobiera tablicê definicji metod klasy 
	function __getMethodsArr(oMethodContainer) {
		var vItemArr = new Array();
		var vItemCount = oMethodContainer.MOF_GetCollectionCount("Operations");
		for(var j=0;j<vItemCount;j++) 		
			vItemArr.push(__getMethodDefStr(oMethodContainer.MOF_GetCollectionItem("Operations",j)));	
		return(vItemArr);
	}
	

	// pobiera tablicê definicji parametrów metody
	function __getMethodParamArr(oMethod) {
		var vItemArr = {'in' : null, 'return' : null};
		vItemArr['in'] = new Array();
		vItemArr['return'] = new Array();
		var vItemCount = oMethod.MOF_GetCollectionCount("Parameters");
		var oItem=null;
		var vDirection=null;
		for(var j=0;j<vItemCount;j++) 
		{
			oItem=oMethod.MOF_GetCollectionItem("Parameters",j); //pobieram parametr
			vDirection=oItem.MOF_GetAttribute("DirectionKind");
			if(vDirection!='pdkReturn') vItemArr['in'].push(__getMethodParamDefStr(oItem));
			else vItemArr['return'].push(__getMethodParamDefStr(oItem));
			oItem=null;
			vDirection=null;
		}				
		return(vItemArr);
	}
	


	// tworzy ci¹g znaków z definicj¹ metody klasy
	function __getMethodDefStr(oMethod)
	{
		var vStrFinal,vStrAbstract,vStrVis,vStrName,vInParamStr,vBodyStr,vIsAbstract,vParamArr;
		
		vStrFinal=(oMethod.MOF_GetAttribute("IsLeaf")=='True')?'final ':'';
		if(oMethod.MOF_GetAttribute("IsAbstract")=="True") {
			vStrAbstract='abstract ';			
			vIsAbstract=true; // aktualna metoda jest abstrakcyjna - nie moze mieæ cia³a
			// ustawiam flage, ¿e klasa ma co najmniej jednb¹ operacjê abstrakcyjn¹
			vHasAbstractOperations=true; 		
		}
		else {
			vStrAbstract='';
			vIsAbstract=false;			
		}
		vStrVis=__getVisibilityStr(oMethod.MOF_GetAttribute("Visibility"));
		vStrName=oMethod.MOF_GetAttribute("Name");			
		vParamArr=__getMethodParamArr(oMethod);
		// parametry nag³ówka operacji
		vInParamStr=vParamArr['in'].join(',');	
		vBodyStr=';';   // metody interfejsu nie maj¹ cia³a                                        
		return(vStrFinal+vStrAbstract+vStrVis+' function '+vStrName+'('+vInParamStr+')'+vBodyStr);
	}

	
	// tworzy ci¹g znaków z definicj¹ parametru metody
	function __getMethodParamDefStr(oParam) {
		
		var vDirection,vType='',vName='';		
		vDirection=oParam.MOF_GetAttribute("DirectionKind");		
		if(vDirection!='pdkReturn')
		{
		    vType=((oParam.TypeExpression.length>0)?(oParam.TypeExpression+' '):'');	
		    if( (vDirection=='pdkInout')||(vDirection=='pdkOut') ) vName+='&$';
		    else vName+='$'; // pdkIn				
		    vName+=oParam.MOF_GetAttribute("Name");
		}	
		else vName=oParam.MOF_GetAttribute("Name");
		return(vType+vName);
	}

	function __getVisibilityStr(vCode) {
		var vVis="";
		switch(vCode) {
			case 'vkPublic'    : { vVis="public"; break;}
			case 'vkProtected' : { vVis="protected"; break;}
			case 'vkPrivate'   : { vVis="private" ;break;} 
			default            : { vVis="public"; break;}
		}
		return(vVis);
	}
}




currentItem = currentItemStack.pop();
// END OF SCRIPT

// TEXT
print("\n");

// REPEAT
currentItemStack.push(currentItem);

try {
    eval('var rootElem = currentItem');
}catch (ex) {
    log('template.cot(325):<@REPEAT@> Error exists in  path expression.');
    throw ex
}
try {
    eval('var elemArr1 = getAllElements(true, rootElem, \'UMLClass\', \'\', \'\')');
}catch (ex) {
    log('template.cot(325):<@REPEAT@> Error exists in path, type, collection name.');
    throw ex
}
try {
    for (var i1 = 0, c1 = elemArr1.length; i1 < c1; i1++ ) {
        currentItem = elemArr1[i1];
        
        // TEXT
        print("");
        
        // SCRIPT
        currentItemStack.push(currentItem);
        
    fileBegin(getTarget()+"\\"+current().Name+".class.php");
    var vClassName=current().Name;
    var oPHPClass= new PHPClassDefinition(current());

        currentItem = currentItemStack.pop();
        // END OF SCRIPT
        
        // TEXT
        print("");
print("<?php\n");        
        // TEXT
        print("\n");
        
        // REPEAT
        currentItemStack.push(currentItem);
        
        try {
            eval('var rootElem = prj');
        }catch (ex) {
            log('template.cot(332):<@REPEAT@> Error exists in :: path expression.');
            throw ex
        }
        try {
            eval('var elemArr2 = getAllElements(true, rootElem, \'UMLGeneralization\', \'\', \'\')');
        }catch (ex) {
            log('template.cot(332):<@REPEAT@> Error exists in path, type, collection name.');
            throw ex
        }
        try {
            for (var i2 = 0, c2 = elemArr2.length; i2 < c2; i2++ ) {
                currentItem = elemArr2[i2];
                
                // TEXT
                print("");
                
                // SCRIPT
                currentItemStack.push(currentItem);
                
	//czytam ewentualnego rodzica bie¿¹cej klasy
	if(vClassName==current().Child.Name) oPHPClass.setParentClass(current().Parent);

                currentItem = currentItemStack.pop();
                // END OF SCRIPT
                
                // TEXT
                print("");
        
            }
        } catch (ex) {
            log('template.cot(332):<@REPEAT@> Error exists in command.');
            throw ex;
        }
        currentItem = currentItemStack.pop();
        // END OF REPEAT
        
        // TEXT
        print("");
        
        // REPEAT
        currentItemStack.push(currentItem);
        
        try {
            eval('var rootElem = prj');
        }catch (ex) {
            log('template.cot(338):<@REPEAT@> Error exists in :: path expression.');
            throw ex
        }
        try {
            eval('var elemArr3 = getAllElements(true, rootElem, \'UMLRealization\', \'\', \'\')');
        }catch (ex) {
            log('template.cot(338):<@REPEAT@> Error exists in path, type, collection name.');
            throw ex
        }
        try {
            for (var i3 = 0, c3 = elemArr3.length; i3 < c3; i3++ ) {
                currentItem = elemArr3[i3];
                
                // TEXT
                print("");
                
                // SCRIPT
                currentItemStack.push(currentItem);
                
	//czytam implementowane interfejsy
	if(vClassName==current().Client.Name) oPHPClass.addImplementedInterface(current().Supplier);

                currentItem = currentItemStack.pop();
                // END OF SCRIPT
                
                // TEXT
                print("");
        
            }
        } catch (ex) {
            log('template.cot(338):<@REPEAT@> Error exists in command.');
            throw ex;
        }
        currentItem = currentItemStack.pop();
        // END OF REPEAT
        
        // TEXT
        print("");
print(oPHPClass.getDefinition());        
        // TEXT
        print("\n");
print("\n?>");        
        // TEXT
        print("\n");
        
        // SCRIPT
        currentItemStack.push(currentItem);
        
    fileEnd();

        currentItem = currentItemStack.pop();
        // END OF SCRIPT
        
        // TEXT
        print("");

    }
} catch (ex) {
    log('template.cot(338):<@REPEAT@> Error exists in command.');
    throw ex;
}
currentItem = currentItemStack.pop();
// END OF REPEAT

// TEXT
print("\n");

// REPEAT
currentItemStack.push(currentItem);

try {
    eval('var rootElem = currentItem');
}catch (ex) {
    log('template.cot(351):<@REPEAT@> Error exists in  path expression.');
    throw ex
}
try {
    eval('var elemArr4 = getAllElements(true, rootElem, \'UMLInterface\', \'\', \'\')');
}catch (ex) {
    log('template.cot(351):<@REPEAT@> Error exists in path, type, collection name.');
    throw ex
}
try {
    for (var i4 = 0, c4 = elemArr4.length; i4 < c4; i4++ ) {
        currentItem = elemArr4[i4];
        
        // TEXT
        print("");
        
        // SCRIPT
        currentItemStack.push(currentItem);
        
    fileBegin(getTarget()+"\\"+current().Name+".iface.php");   
    var oPHPInterface= new PHPInterfaceDefinition(current());

        currentItem = currentItemStack.pop();
        // END OF SCRIPT
        
        // TEXT
        print("");
print("<?php\n");        
        // TEXT
        print("\n");
print(oPHPInterface.getDefinition());        
        // TEXT
        print("\n");
print("\n?>");        
        // TEXT
        print("\n");
        
        // SCRIPT
        currentItemStack.push(currentItem);
        
    fileEnd();

        currentItem = currentItemStack.pop();
        // END OF SCRIPT
        
        // TEXT
        print("");

    }
} catch (ex) {
    log('template.cot(352):<@REPEAT@> Error exists in command.');
    throw ex;
}
currentItem = currentItemStack.pop();
// END OF REPEAT

// TEXT
print("");

// TEXT
print("");

// TEXT
print("");

// TEXT
print("");

// TEXT
print("");

// TEXT
print("");

// TEXT
print("");

// TEXT
print("");
