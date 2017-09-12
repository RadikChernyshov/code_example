import React, {Component} from 'react';
import {bindActionCreators} from 'redux';
import {connect} from 'react-redux';
import {Link} from 'react-router-dom';
import {
	addFavourite,
	fetchPersons,
	removeFavourite,
	sortPersons,
} from '../../actions/PersonsActions';

const mapStateToProps = state => ({
	personsReducer: state.personsReducer,
});

const mapDispatchToProps = dispatch => bindActionCreators({
	fetchPersons, addFavourite, removeFavourite, sortPersons
}, dispatch);

class Home extends Component {
	componentWillMount() {
		this.props.fetchPersons();
	}
	
	addFavourite(id) {
		this.props.addFavourite(id);
	}
	
	removeFavourite(id) {
		this.props.removeFavourite(id);
	}
	
	sortPersonsGrid(key) {
		this.props.sortPersons(key);
	}
	
	renderRow = (person) => {
		const isFavourite = -1 === this.props.personsReducer.favouriteIds.indexOf(person.id);
		return (
			<tr key={person.id}>
				<td>{person.id}</td>
				<td className={'text-capitalize'}>{person.name}</td>
				<td>{person.mass}</td>
				<td>{person.height}</td>
				<td className={'text-center'}>
					{isFavourite ?
						<Link to={'#'} onClick={this.addFavourite.bind(this, person.id)} className={'btn btn-outline-primary btn-sm'}>Add to Favourites</Link> :
						<Link to={'#'} onClick={this.removeFavourite.bind(this, person.id)} className={'btn btn-outline-danger btn-sm'}>Remove from Favourites</Link>}
				</td>
				<td className={'text-center'}>
					<Link to={`/details/${person.id}`}
						  className={'btn btn-outline-success btn-sm'}>
						Details
					</Link>
				</td>
			</tr>
		);
	};
	
	render() {
		return (
			<div className={'app-template'}>
				<div className={'container'}>
					<h1>Home</h1>
					<table className={'table table-striped table-hover'}>
						<thead>
							<tr>
								<td><Link to={'#'} onClick={this.sortPersonsGrid.bind(this, 'id')}>#</Link></td>
								<td><Link to={'#'} onClick={this.sortPersonsGrid.bind(this, 'name')}>Name</Link></td>
								<td><Link to={'#'} onClick={this.sortPersonsGrid.bind(this, 'mass')}>Mass</Link></td>
								<td><Link to={'#'} onClick={this.sortPersonsGrid.bind(this, 'height')}>Height</Link></td>
								<td className={'button-col-mid'}>&nbsp;</td>
								<td className={'button-col'}>&nbsp;</td>
							</tr>
						</thead>
						<tbody>
							{this.props.personsReducer.allIds.map(id => this.renderRow(this.props.personsReducer.list[id]))}
						</tbody>
					</table>
				</div>
			</div>
		);
	}
}

export default connect(
	mapStateToProps,
	mapDispatchToProps,
)(Home);
