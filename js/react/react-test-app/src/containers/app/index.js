import React from 'react';
import {Link, Route} from 'react-router-dom';
import Home from '../home';
import Favorites from '../favorites';
import Details from '../details';

const App = () => (
	<div>
		<nav className={'navbar navbar-expand-md navbar-dark bg-dark fixed-top'}>
			<div className={'collapse navbar-collapse'}>
				<ul className={'navbar-nav mr-auto'}>
					<li className={'nav-item'}>
						<Link to="/" className={'nav-link'}>Home</Link>
					</li>
					<li className={'nav-item'}>
						<Link to="/favorites" className={'nav-link'}>Favorites</Link>
					</li>
				</ul>
			</div>
		</nav>
		<main>
			<Route exact path="/" component={Home}/>
			<Route exact path="/favorites" component={Favorites}/>
			<Route exact path="/details/:personId" component={Details}/>
		</main>
	</div>
);

export default App;
