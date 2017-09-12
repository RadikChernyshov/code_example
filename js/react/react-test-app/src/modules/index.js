import {combineReducers} from 'redux';
import {routerReducer} from 'react-router-redux';
import personsReducer from './personsReducer';

export default combineReducers({
	router: routerReducer,
	personsReducer,
});
