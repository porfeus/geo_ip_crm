/**
 * This pagination plug-in provides a `dt-tag select` menu with the list of the page
 * numbers that are available for viewing.
 *
 *  @name Select list
 *  @summary Show a `dt-tag select` list of pages the user can pick from.
 *  @author _jneilliii_
 *
 *  @example
 *    $(document).ready(function() {
 *        $('#example').dataTable( {
 *            "sPaginationType": "listbox"
 *        } );
 *    } );
 */

$.fn.dataTableExt.oPagination.listbox = {
	/*
	 * Function: oPagination.listbox.fnInit
	 * Purpose:  Initalise dom elements required for pagination with listbox input
	 * Returns:  -
	 * Inputs:   object:oSettings - dataTables settings object
	 *             node:nPaging - the DIV which contains this pagination control
	 *             function:fnCallbackDraw - draw function which must be called on update
	 */
	"fnInit": function (oSettings, nPaging, fnCallbackDraw) {
		$(nPaging).prepend($("<ul class=\"pagination\"></ul>"));
		var ul = $("ul", $(nPaging));
		nPrevious = document.createElement('li');
		nNext = document.createElement('li');
		$(nPrevious).append($('<a href="javascript:void(0)">' + (oSettings.oLanguage.oPaginate.sPrevious) + '</a>'));
		$(nNext).append($('<a href="javascript:void(0)">' + (oSettings.oLanguage.oPaginate.sNext) + '</a>'));
		nPrevious.className = "paginate_button previous";
		nNext.className = "paginate_button next";
		ul.append(nPrevious);

		$(nPrevious).click(function () {
				if (!(oSettings._iDisplayStart === 0)) {
						oSettings.oApi._fnPageChange(oSettings, "previous");
						fnCallbackDraw(oSettings);
				}
		});

		$(nNext).click(function () {
				if (!(oSettings.fnDisplayEnd() == oSettings.fnRecordsDisplay()
						||
						oSettings.aiDisplay.length < oSettings._iDisplayLength)) {
						oSettings.oApi._fnPageChange(oSettings, "next");
						fnCallbackDraw(oSettings);
				}
		});
		$(nPrevious).bind('selectstart', function () { return false; });
		$(nNext).bind('selectstart', function () { return false; });
		$(oSettings.nTable).DataTable().on('length.dt', function (e, settings, len) {
				$("li.dynamic_page_item", nPaging).remove();
		});

		$(oSettings.nTable).DataTable().on('search.dt', function (e, settings, len) {
				$("li.dynamic_page_item", nPaging).remove();
		});


		var nInput = document.createElement('select');
		nInput.className = "form-control";
		if (oSettings.sTableId !== '') {
			nPaging.setAttribute('id', oSettings.sTableId + '_paginate');
		}
		nInput.style.display = "inline";


		nInputLI = document.createElement('li');
		nInputLISpan = document.createElement('span');
		$(nInputLISpan).css('padding', '0px');
		nInputLI.className = "paginate_button";
		$(nInputLISpan).append(nInput);
		$(nInputLI).append(nInputLISpan);
		ul.append(nInputLI);
		ul.append(nNext);

		$(nInput).change(function (e) { // Set DataTables page property and redraw the grid on listbox change event.
			//window.scroll(0,0); //scroll to top of page
			if (this.value === "" || this.value.match(/[^0-9]/)) { /* Nothing entered or non-numeric character */
				return;
			}
			var iNewStart = oSettings._iDisplayLength * (this.value - 1);
			if (iNewStart > oSettings.fnRecordsDisplay()) { /* Display overrun */
				oSettings._iDisplayStart = (Math.ceil((oSettings.fnRecordsDisplay() - 1) / oSettings._iDisplayLength) - 1) * oSettings._iDisplayLength;
				fnCallbackDraw(oSettings);
				return;
			}
			oSettings._iDisplayStart = iNewStart;
			fnCallbackDraw(oSettings);
		});
	},

	/*
	 * Function: oPagination.listbox.fnUpdate
	 * Purpose:  Update the listbox element
	 * Returns:  -
	 * Inputs:   object:oSettings - dataTables settings object
	 *             function:fnCallbackDraw - draw function which must be called on update
	 */
	"fnUpdate": function (oSettings, fnCallbackDraw) {
		if (!oSettings.aanFeatures.p) {
			return;
		}

    /* Loop over each instance of the pager */
    var an = oSettings.aanFeatures.p;
    for (var i = 0, iLen = an.length ; i < iLen ; i++) {
        var buttons = an[i].getElementsByTagName('li');
        $(buttons).removeClass("active");

        if (oSettings._iDisplayStart === 0) {
            buttons[0].className = "paginate_buttons disabled previous";
            buttons[buttons.length - 1].className = "paginate_button next";
        } else {
            buttons[0].className = "paginate_buttons previous";
        }

        if (oSettings.fnDisplayEnd() == oSettings.fnRecordsDisplay()
            ||
            oSettings.aiDisplay.length < oSettings._iDisplayLength) {
            buttons[buttons.length - 1].className = "paginate_button disabled next";
        }
    }


		var iPages = Math.ceil((oSettings.fnRecordsDisplay()) / oSettings._iDisplayLength);
		var iCurrentPage = Math.ceil(oSettings._iDisplayStart / oSettings._iDisplayLength) + 1; /* Loop over each instance of the pager */
		var an = oSettings.aanFeatures.p;
		for (var i = 0, iLen = an.length; i < iLen; i++) {
			var inputs = an[i].getElementsByTagName('select');
			var elSel = inputs[0];
			if(elSel.options.length != iPages) {
				elSel.options.length = 0; //clear the listbox contents
				for (var j = 0; j < iPages; j++) { //add the pages
					var oOption = document.createElement('option');
					oOption.text = j + 1;
					oOption.value = j + 1;
					try {
						elSel.add(oOption, null); // standards compliant; doesn't work in IE
					} catch (ex) {
						elSel.add(oOption); // IE only
					}
				}
			}
		  elSel.value = iCurrentPage;
		}
	}
};
