import {
	ADD_FAVOURITE,
	FETCH_PERSON_SUCCESS,
	FETCH_PERSONS_SUCCESS,
	REMOVE_FAVOURITE,
	SORT,
} from '../constants';

const initialState = {
	list: {},
	allIds: [],
	favouriteIds: [],
	single: {},
	sort: {},
};

const fetchPersons = (state, personsData) => {
	const personsList = state.list;
	const personsIds = state.allIds;
	personsData.forEach((person, key) => {
		person.id = key + 1;
		person.mass = parseInt(person.mass, 10);
		person.height = parseInt(person.height, 10);
		if (!personsList[person.id]) {
			personsList[person.id] = person;
		}
		if (-1 === personsIds.indexOf(person.id)) {
			personsIds.push(person.id);
		}
	});
	
	return {
		list: personsList,
		allIds: personsIds,
	};
};

const fetchPerson = (state, data) => {
	return {
		single: Object.assign(state.single, data),
	};
};

const sortPersons = (state, key) => {
	const personsIds = [];
	const sortDirection = state.sort;
	const sorting = (property) => {
		let sortOrder = 1;
		if (property[0] === '-') {
			sortOrder = -1;
			property = property.substr(1);
		}
		return (a, b) => {
			let result = (a[property] < b[property]) ? -1 : (a[property] > b[property]) ? 1 : 0;
			if (sortDirection[key]) {
				result = (a[property] > b[property]) ? -1 : (a[property] < b[property]) ? 1 : 0;
			}
			return result * sortOrder;
		};
	};
	sortDirection[key] = sortDirection.hasOwnProperty(key) ? !sortDirection[key] : true;
	[].concat(Object.values(state.list))
		.sort(sorting(key))
		.map(item => personsIds.push(item.id));
	return {
		allIds: personsIds,
		sort: sortDirection,
	};
};

const addFavouriteItem = (state, id) => {
	const index = state.favouriteIds.indexOf(id);
	if (-1 === index) {
		state.favouriteIds.push(id);
	}
	return {
		favouriteIds: state.favouriteIds,
	};
};

const removeFavouriteItem = (state, id) => {
	const index = state.favouriteIds.indexOf(id);
	if (index > -1) {
		state.favouriteIds.splice(index, 1);
	}
	return {
		favouriteIds: state.favouriteIds,
	};
};

export default (state = initialState, action) => {
	switch (action.type) {
		case FETCH_PERSONS_SUCCESS:
			return {
				...state,
				...fetchPersons(state, action.payload)
			};
		case FETCH_PERSON_SUCCESS:
			return {
				...state,
				...fetchPerson(state, action.payload)
			};
		case ADD_FAVOURITE:
			return {
				...state,
				...addFavouriteItem(state, action.payload)
			};
		case REMOVE_FAVOURITE:
			return {
				...state,
				...removeFavouriteItem(state, action.payload)
			};
		case SORT:
			return {
				...state,
				...sortPersons(state, action.payload)
			};
		default:
			return state;
	}
}