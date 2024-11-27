import { call, put, select, take } from 'redux-saga/effects';
import { push } from '../../../lib/redux-router';

import { authenticate, authenticateUsingOidcCallback } from './login';
import selectors from '../../../selectors';
import ActionTypes from '../../../constants/ActionTypes';
import Paths from '../../../constants/Paths';

export function* goToLogin() {
  const params = new URLSearchParams(
    window.location.hash.substring(1) || window.location.search,
  );
  const emailOrUsername = params.get('emailOrUsername');
  const password = params.get('password');

  // todo : rajouter un &redirect= et le gérer en post login
  let url = Paths.LOGIN;
  if (emailOrUsername != '')
  {
    url += "?emailOrUsername="+emailOrUsername+"&password="+password);
  }
  yield put(push(url); //
}

export function* goToRoot() {
  yield put(push(Paths.ROOT));
}

export function* handleLocationChange() {
  const pathsMatch = yield select(selectors.selectPathsMatch);

  if (!pathsMatch) {
    return;
  }

  switch (pathsMatch.pattern.path) {
    case Paths.ROOT:
    case Paths.PROJECTS:
    case Paths.BOARDS:
    case Paths.CARDS:
      yield call(goToLogin);

      break;
    // Baptiste : Patch login without username : https://github.com/plankanban/planka/discussions/577
    case Paths.LOGIN: {
      const params = new URLSearchParams(
        window.location.hash.substring(1) || window.location.search,
      );

      const emailOrUsername = params.get('emailOrUsername');
      const password = params.get('password');

      if (emailOrUsername && password) {
        yield call(authenticate, {
          emailOrUsername,
          password,
        });
      }

      break;
    }
    case Paths.OIDC_CALLBACK: {
      const isInitializing = yield select(selectors.selectIsInitializing);

      if (isInitializing) {
        yield take(ActionTypes.LOGIN_INITIALIZE);
      }

      yield call(authenticateUsingOidcCallback);

      break;
    }
    default:
  }
}

export default {
  goToLogin,
  goToRoot,
  handleLocationChange,
};
