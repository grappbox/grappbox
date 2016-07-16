package com.grappbox.grappbox.grappbox.BugTracker;

import android.graphics.Color;

import org.json.JSONException;
import org.json.JSONObject;

import java.util.Random;

/**
 * Created by wieser_m on 18/02/2016.
 */
public class TagEntity {
    String  _id;
    String  _name;
    String  _projectId;
    String _color;
    boolean _onBug;

    public TagEntity()
    {
        _id = "";
    }

    private String randomColor()
    {
        String[] chars = {"a", "b", "c", "d", "e", "f"};
        Random rnd = new Random();
        String ret = "";

        while (ret.length() < 6)
        {
            int rand = rnd.nextInt(6);
            if (rand >= 6)
                rand = 6;
            ret += chars[rand];
        }
        return ret;
    }

    public TagEntity(JSONObject data) throws JSONException
    {
        _name = data.getString("name");
        _id = data.getString("id");
        if (data.has("projectId"))
            _projectId = data.getString("projectId");
        Random rnd = new Random();
        _color = "#" + randomColor(); //TODO : Change with API Color when API did it...
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

    public void SetColor(String color){ _color = color;}

    public String GetColor(){ return _color; }
}
