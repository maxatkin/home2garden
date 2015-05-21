function mm_menu()
{
	var parentClass='isParent';				
	var activeParentClass='isActive';		
	var preventHoverClass='nohover';		
	var indicateJSClass='dhtml';			
	var toHideClass='hiddenChild';			
	var toShowClass='shownChild';			
	var currentClass='current';				
	var d=document.getElementById('nav');	
// if DOM is not available stop right here.
	if(!document.getElementById && !document.createTextNode){return;}

// if the navigation element is available, apply the class denoting DHTML capabilities
	if(d)
	{
		d.className+=d.className==''?indicateJSClass:' '+indicateJSClass;
		var lis,i,firstUL,j,apply;

// loop through all LIs and check which ones have a nested UL
		lis=d.getElementsByTagName('li');
		for(i=0;i<lis.length;i++)
		{
			firstUL=lis[i].getElementsByTagName('ul')[0]
// if there is a nested UL, deactivate the first nested link and apply the class to show 
// there is a nested list
			if(firstUL)
			{
				lis[i].childNodes[0].onclick=function(){return false;}
				lis[i].className+=lis[i].className==''?parentClass:' '+parentClass;
// check if there is a "current" element 
				apply=true;
				if(new RegExp('\\b'+currentClass+'\\b').test(lis[i].className)){apply=false;}
				if(apply)
				{
					for(j=0;j<firstUL.getElementsByTagName('li').length;j++)
					{
						if(new RegExp('\\b'+currentClass+'\\b').test(firstUL.getElementsByTagName('li')[j].className)){apply=false;break}
					}
				}
// if there is no current element, apply the class to hide the nested list
				if(apply)
				{
					firstUL.className+=firstUL.className==''?toHideClass:' '+toHideClass;
// check if there is a class to prevent hover effects and only apply the function
// onclick if that is the case, otherwise apply it onclick and onhover
					if(new RegExp('\\b'+preventHoverClass+'\\b').test(d.className))
					{
						lis[i].onclick=function(){domm_menu(this);}
					} else {
						lis[i].onclick=function(){domm_menu(this);}
						lis[i].onmouseover=function(){domm_menu(this);}
						lis[i].onmouseout=function(){domm_menu(null);}
					}
// if there is a current element, define the list as being kept open and apply the 
// classes to show the nested list and define the parent LI as an active one
				} else {
					lis[i].keepopen=1;
					firstUL.className+=firstUL.className==''?toShowClass:' '+toShowClass;
					lis[i].className=lis[i].className.replace(parentClass,activeParentClass);
				}
			}
		}
	}
// function to show and hide the nested lists and add the classes to the parent LIs
	function domm_menu(o)
	{
		var childUL,isobj,swap;

// loop through all LIs of the navigation		
		lis=d.getElementsByTagName('li');
		for(i=0;i<lis.length;i++)
		{
			isobj=lis[i]==o;
// function to exchange class names in an object
			swap=function(tmpobj,tmporg,tmprep)
			{
				tmpobj.className=tmpobj.className.replace(tmporg,tmprep)		
			}
// if the current LI does not have an indicator to be kept visible
			if(!lis[i].keepopen)
			{
				childUL=lis[i].getElementsByTagName('ul')[0];
// check if there is a nested UL and if the current LI is not the one clicked on
// and exchange the classes accordingly (ie. hide all other nested lists and 
// make the LIs parent rather than active.
				if(childUL)	
				{	
					if(new RegExp('\\b'+preventHoverClass+'\\b').test(d.className))
					{
						if(new RegExp('\\b'+activeParentClass+'\\b').test(lis[i].className))
						{
							swap(childUL,isobj?toShowClass:toHideClass,isobj?toHideClass:toShowClass);		
							swap(lis[i],isobj?activeParentClass:parentClass,isobj?parentClass:activeParentClass);		
						} else {
	
							swap(childUL,isobj?toHideClass:toShowClass,isobj?toShowClass:toHideClass);		
							swap(lis[i],isobj?parentClass:activeParentClass,isobj?activeParentClass:parentClass);		
						}
					} else {
							swap(childUL,isobj?toHideClass:toShowClass,isobj?toShowClass:toHideClass);		
							swap(lis[i],isobj?parentClass:activeParentClass,isobj?activeParentClass:parentClass);		
					}
				} 
			}
		}
	}
}
window.onload=function()
{
	mm_menu();
}