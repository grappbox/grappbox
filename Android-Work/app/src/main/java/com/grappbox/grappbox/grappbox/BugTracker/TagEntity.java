package com.grappbox.grappbox.grappbox.BugTracker;

import org.json.JSONException;
import org.json.JSONObject;

/**
 * Created by wieser_m on 18/02/2016.
 */
public class TagEntity {
    String  _id;
    String  _name;
    String  _projectId;
    boolean _onBug;

    public TagEntity()
    {
        _id = "";
    }

    public TagEntity(JSONObject data) throws JSONException
    {
        _name = data.getString("name");
        _id = data.getString("id");
        if (data.has("projectId"))
            _projectId = data.getString("projectId");
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

    public String GetProjectId() { return _projectId; }

    public void SetProjectId(String projectId){ _projectId = projectId; }

    public void SetOnBug(boolean onbug)
    {
        _onBug = onbug;
    }
}
