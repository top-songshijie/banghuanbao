<<<<<<< HEAD
var Length = document.documentElement.clientWidth;
var baseWidth = Length <= 1024 ? Length : 1024 < Length ? 750 : '';
document.documentElement.style.fontSize = baseWidth / 750 * 100 + 'px';


//正则表达式

//window.onload = function() {
//	var overscroll = function(el) {
//		el.addEventListener('touchstart', function() {
//			var top = el.scrollTop,
//				totalScroll = el.scrollHeight,
//				currentScroll = top + el.offsetHeight
//			if(top === 0) {
//				el.scrollTop = 1
//			} else if(currentScroll === totalScroll) {
//				el.scrollTop = top - 1
//			}
//		})
//		el.addEventListener('touchmove', function(evt) {
//			//if the content is actually scrollable, i.e. the content is long enough
//			//that scrolling can occur
//			if(el.offsetHeight < el.scrollHeight)
//				evt._isScroller = true
//		})
//	}
//	overscroll(document.querySelector('.container'));
//	document.body.addEventListener('touchmove', function(evt) {
//		//In this case, the default behavior is scrolling the body, which
//		//would result in an overflow.  Since we don't want that, we preventDefault.
//		if(!evt._isScroller) {
//			evt.preventDefault()
//		}
//	})
	
	
	
	//
//	mui('body').on('tap','a',function(){
//		mui.openWindow(this.href)
//	})

//}


=======
var Length = document.documentElement.clientWidth;
var baseWidth = Length <= 1024 ? Length : 1024 < Length ? 750 : '';
document.documentElement.style.fontSize = baseWidth / 750 * 100 + 'px';


//正则表达式

//window.onload = function() {
//	var overscroll = function(el) {
//		el.addEventListener('touchstart', function() {
//			var top = el.scrollTop,
//				totalScroll = el.scrollHeight,
//				currentScroll = top + el.offsetHeight
//			if(top === 0) {
//				el.scrollTop = 1
//			} else if(currentScroll === totalScroll) {
//				el.scrollTop = top - 1
//			}
//		})
//		el.addEventListener('touchmove', function(evt) {
//			//if the content is actually scrollable, i.e. the content is long enough
//			//that scrolling can occur
//			if(el.offsetHeight < el.scrollHeight)
//				evt._isScroller = true
//		})
//	}
//	overscroll(document.querySelector('.container'));
//	document.body.addEventListener('touchmove', function(evt) {
//		//In this case, the default behavior is scrolling the body, which
//		//would result in an overflow.  Since we don't want that, we preventDefault.
//		if(!evt._isScroller) {
//			evt.preventDefault()
//		}
//	})
	
	
	
	//
//	mui('body').on('tap','a',function(){
//		mui.openWindow(this.href)
//	})

//}


>>>>>>> 9c781cc2cf65170209d1936be01b7acac8aa5bb9
