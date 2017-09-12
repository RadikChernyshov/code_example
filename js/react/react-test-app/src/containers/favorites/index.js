import React, {Component} from 'react';
import {bindActionCreators} from 'redux';
import {connect} from 'react-redux';
import {Link} from 'react-router-dom';
import {fetchPersons, removeFavourite} from '../../actions/PersonsActions';

const mapStateToProps = state => ({
	personsReducer: state.personsReducer,
});

const mapDispatchToProps = dispatch => bindActionCreators({
	fetchPersons, removeFavourite,
}, dispatch);

class Favorites extends Component {
	removeFavourite(id) {
		this.props.removeFavourite(id);
	}
	
	renderRow = (person) => {
		return (
			<tr key={person.id}>
				<td>{person.id}</td>
				<td className={'text-capitalize'}>{person.name}</td>
				<td>{person.height}</td>
				<td>{person.mass}</td>
				<td className={'text-center'}>
					<Link to={`/details/${person.id}`}
						  className={'btn btn-outline-success btn-sm'}>
						Details
					</Link>
				</td>
				<td className={'text-center'}>
					<Link to={'#'}
						  onClick={this.removeFavourite.bind(this, person.id)}
						  className={'btn btn-outline-danger btn-sm'}>
						Remove
					</Link>
				</td>
			</tr>
		);
	};
	
	render() {
		return (
			<div className={'container'}>
				<h1>Favorites</h1>
				<table className={'table table-striped table-hover'}>
					<thead>
					<tr>
						<td>#</td>
						<td>Name</td>
						<td>Mass</td>
						<td>Height</td>
						<td className={'button-col'}>&nbsp;</td>
						<td className={'button-col'}>&nbsp;</td>
					</tr>
					</thead>
					<tbody>
						{this.props.personsReducer.favouriteIds.map(id => this.renderRow(this.props.personsReducer.list[id]))}
					</tbody>
				</table>
			</div>
		);
	}
}

export default connect(
	mapStateToProps,
	mapDispatchToProps,
)(Favorites);

