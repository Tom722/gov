const install = function(vm) {
	uni.$u.routeIntercept = function(route,resolve) {

		// console.log(route)

		resolve(true);
	}
}

export default {
	install
}
