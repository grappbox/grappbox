package com.grappbox.grappbox.grappbox.BugTracker;

import org.json.JSONException;
import org.json.JSONObject;

/**
 * Created by wieser_m on 18/02/2016.
 */
public class UserEntity {
    String  _id;
    String  _name;
    String  _email;
    String  _avatar;
    boolean _onBug;

    public UserEntity()
    {
        _id = "";
    }

    public UserEntity(JSONObject data) throws JSONException
    {
        _id = data.getString("id");
        if (data.has("name"))
            _name = data.getString("name");
        else
            _name = data.getString("firstname") + " " + data.getString("lastname");
        if (data.has("email"))
            _email = data.getString("email");
        if (data.has("avatar"))
            _avatar = data.getString("avatar");
    }

    public boolean IsValid()
    {
        return !_id.isEmpty();
    }

    public String GetId() {
        return _id;
    }

    public void SetId(String _id) {
        this._id = _id;
    }

    public String GetName() {
        return _name;
    }

    public void SetName(String _name) {
        this._name = _name;
    }

    public String GetEmail() {
        return _email;
    }

    public void SetEmail(String _email) {
        this._email = _email;
    }

    public void SetOnBug(boolean onbug)
    {
        _onBug = onbug;
    }
}
