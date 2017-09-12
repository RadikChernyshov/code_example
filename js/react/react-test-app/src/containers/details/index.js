import React, {Component} from 'react';
import {bindActionCreators} from 'redux';
import {connect} from 'react-redux';
import {addFavourite, fetchPerson} from '../../actions/PersonsActions';

const mapStateToProps = state => ({
	personsReducer: state.personsReducer,
});

const mapDispatchToProps = dispatch => bindActionCreators({
	fetchPerson, addFavourite,
}, dispatch);

class Details extends Component {
	componentWillMount() {
		this.props.fetchPerson(this.props.match.params.personId);
	}
	
	renderRow = (rowKey, rowValue) => {
		const value = rowValue instanceof Array ? rowValue.join('\r\n') : rowValue;
		const key = rowKey.replace('_', ' ');
		const isRowData = rowValue instanceof Array;
		return isRowData ? null : (
			<tr key={rowKey}>
				<td className={'text-left text-capitalize'}>
					<strong>{key}</strong>
				</td>
				<td className={'text-left'}>{value}</td>
			</tr>
		);
	};
	
	render() {
		const singlePersonInfo = [];
		for (let item in this.props.personsReducer.single) {
			singlePersonInfo.push(
				this.renderRow(item, this.props.personsReducer.single[item]));
		}
		return (
			<div className={'app-template'}>
				<div className={'container'}>
					<h1>{this.props.personsReducer.single.name}</h1>
					<table className={'table table-striped'}>
						<tbody>
							{singlePersonInfo}
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
)(Details);
