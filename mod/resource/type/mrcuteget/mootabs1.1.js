var mootabs = new Class({
	
	initialize: function(element, options) {
		this.options = Object.extend({
			/*width:				300,
			height:				200,*/
			changeTransition:	Fx.Transitions.Bounce.easeOut,
			duration: 1000,
			mouseOverClass:	'active'
		}, options || {});
		
		this.el = $(element);
		this.elid = element;
		
		this.el.setStyles({
			/*height: this.options.height + 'px',
			width: this.options.width + 'px'*/
			height: '100%',
			width: '100%'
		});
		
		this.titles = $$('#' + this.elid + ' ul li.mootabs_button');
		this.titles[0].addClass('active');
		this.activeTitle = this.titles[0];
		
		this.panelHeight = this.options.height - (this.titles[0].getStyle('height').toInt() + 4);
		
		this.panels = $$('#' + this.elid + ' .mootabs_panel');
		this.panels.setStyle('height', this.panelHeight);
		this.panels[0].addClass('active');
		
		
		this.titles.each(function(item) {
			item.addEvent('click', function(){
					item.removeClass(this.options.mouseOverClass);
					this.activate(item);
				}.bind(this)
			);
			
			item.addEvent('mouseover', function() {
				if(item != this.activeTitle)
				{
					item.addClass(this.options.mouseOverClass);
				}
			}.bind(this));
			
			item.addEvent('mouseout', function() {
				if(item != this.activeTitle)
				{
					item.removeClass(this.options.mouseOverClass);
				}
			}.bind(this));
		}.bind(this));
		
		

	},
	
	activate: function(tab){
		if($type(tab) == 'string') 
		{
			myTab = $$('#' + this.elid + ' ul li').filterByAttribute('title', '=', tab)[0];
			tab = myTab;
		}
		
		if($type(tab) == 'element')
		{
			var newTab = tab.getProperty('title');
			this.panels.removeClass('active');
			this.panels.filterById(newTab).addClass('active');
			if(this.options.changeTransition != 'none')
			{
				this.panels.filterById(newTab).setStyle('height', 0);
				var changeEffect = new Fx.Elements(this.panels.filterById(newTab), {duration: this.options.duration, transition: this.options.changeTransition});
				changeEffect.start({
					'0': {
						'height': [0, this.panelHeight]
					}
				});
			}
			
			this.titles.removeClass('active');
			
			tab.addClass('active');
			
			this.activeTitle = tab;
		}
	},
	
	addTab: function(title, label, content){
		//the new title
		var newTitle = new Element('li', {
			'title': title
		});
		newTitle.appendText(label);
		this.titles.include(newTitle);
		$$('#' + this.elid + ' ul').adopt(newTitle);
		newTitle.addEvent('click', function() {
			this.activate(newTitle);
		}.bind(this));
		
		newTitle.addEvent('mouseover', function() {
			if(newTitle != this.activeTitle)
			{
				newTitle.addClass(this.options.mouseOverClass);
			}
		}.bind(this));
		newTitle.addEvent('mouseout', function() {
			if(newTitle != this.activeTitle)
			{
				newTitle.removeClass(this.options.mouseOverClass);
			}
		}.bind(this));
		//the new panel
		var newPanel = new Element('div', {
			'style': {'height': this.options.panelHeight},
			'id': title,
			'class': 'mootabs_panel'
		});
		newPanel.setHTML(content);
		this.panels.include(newPanel);
		this.el.adopt(newPanel);
	},
	
	removeTab: function(title){
		if(this.activeTitle.title == title)
		{
			this.activate(this.titles[0]);
		}
		$$('#' + this.elid + ' ul li').filterByAttribute('title', '=', title)[0].remove();
		
		$$('#' + this.elid + ' .mootabs_panel').filterById(title)[0].remove();
	}
	
	
});