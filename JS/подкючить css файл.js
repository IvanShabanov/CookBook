function includeCSS(aFile, aRel) {
	let head = document.getElementsByTagName('head')[0]
	let style = document.createElement('link')
	style.href = aFile
	style.rel = aRel || 'stylesheet'
	head.appendChild(style)
}