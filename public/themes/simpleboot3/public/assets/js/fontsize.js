! function(a) {
	function b() {
		var b = a.document,
			c = b.documentElement,
			d = c.getBoundingClientRect().width;
		document.documentElement.style.fontSize = 100 * (d / 750) + "px"
	}
	window.addEventListener("DOMContentLoaded", function() {
		b()
	}, !1), window.addEventListener("resize", function() {
		b()
	}), b()
}(window);