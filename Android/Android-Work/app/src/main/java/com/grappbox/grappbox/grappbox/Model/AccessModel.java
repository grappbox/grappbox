package com.grappbox.grappbox.grappbox.Model;

import java.util.HashMap;

/**
 * Created by wieser_m on 18/06/2016.
 */
public class AccessModel {
    public enum AccessRights{
        NONE (0),
        READ (1),
        READ_WRITE (2);

        private int _authorizationLevel;
        AccessRights(int authorizationLevel) { _authorizationLevel = authorizationLevel; }
        public int AuthorizationLevel() { return _authorizationLevel; }

        public static AccessRights valueOf(int value) {
            if (value == 1)
                return READ;
            if (value == 2)
                return READ_WRITE;
            return NONE;
        }
    }

    private HashMap<String, AccessRights> _authorizations;

    public AccessModel()
    {
        _authorizations = new HashMap<>();
    }

    public void setAuthorization(String moduleName, AccessRights level)
    {
        if (!_authorizations.containsKey(moduleName))
        {
            _authorizations.put(moduleName, level);
            return;
        }
        if (_authorizations.get(moduleName).AuthorizationLevel() < level.AuthorizationLevel())
            _authorizations.put(moduleName, level);
    }

    public AccessRights getAuthorization(String moduleName)
    {
        return _authorizations.get(moduleName);
    }
}
