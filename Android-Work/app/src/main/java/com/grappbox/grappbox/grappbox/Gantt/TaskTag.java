package com.grappbox.grappbox.grappbox.Gantt;

import org.json.JSONException;
import org.json.JSONObject;

/**
 * Created by wieser_m on 22/04/2016.
 */
public class TaskTag {
    private String id, name;

    public TaskTag(String id, String name) {
        this.id = id;
        this.name = name;
    }

    public TaskTag(JSONObject data) throws JSONException
    {
        id = data.getString("id");
        name = data.getString("name");
    }

    public boolean isValid()
    {
        return !id.equals("");
    }

    public String getId() {
        return id;
    }

    public void setId(String id) {
        this.id = id;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }
}
