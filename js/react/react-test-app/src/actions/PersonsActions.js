import axios from 'axios-es6';
import {
	ADD_FAVOURITE,
	FETCH_PERSON_SUCCESS,
	FETCH_PERSONS_SUCCESS,
	FETCHING,
	REMOVE_FAVOURITE,
	SORT,
} from '../constants';

export const fetchPersons = () => {
	return dispatch => {
		dispatch({
			type: FETCHING,
		});
		return axios.get('https://swapi.co/api/people').then(res => {
			return dispatch({
				type: FETCH_PERSONS_SUCCESS,
				payload: res.data.results,
			});
		});
	};
};

export const fetchPerson = (personId) => {
	return (dispatch, getState) => {
		const personsList = getState().personsReducer.list || [];
		if (personsList[personId]) {
			return dispatch({
				type: FETCH_PERSON_SUCCESS,
				payload: personsList[personId],
			});
		}
		dispatch({
			type: FETCHING,
		});
		return axios.get(`https://swapi.co/api/people/${personId}`)
			.then(res => {
				res.data.id = parseInt(personId, 10);
				return dispatch({
					type: FETCH_PERSON_SUCCESS,
					payload: res.data,
				});
			});
	};
};

export const addFavourite = (personId) => {
	return dispatch => {
		return dispatch({
			type: ADD_FAVOURITE,
			payload: personId,
		});
	};
};

export const sortPersons = (key) => {
	return dispatch => {
		return dispatch({
			type: SORT,
			payload: key,
		});
	};
};

export const removeFavourite = (personId) => {
	return dispatch => {
		return dispatch({
			type: REMOVE_FAVOURITE,
			payload: personId,
		});
	};
};