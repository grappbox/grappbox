package com.grappbox.grappbox.grappbox.Gantt;

import org.json.JSONException;
import org.json.JSONObject;

/**
 * Created by wieser_m on 22/04/2016.
 */
public class TaskUser {
    private String firstname, lastname, id;
    private float resourceTaskUsage;

    public TaskUser(String firstname, String lastname, String id, float resourceTaskUsage) {
        this.firstname = firstname;
        this.lastname = lastname;
        this.id = id;
        this.resourceTaskUsage = resourceTaskUsage;
    }

    public TaskUser(JSONObject data) throws JSONException
    {
        firstname = data.getString("firstname");
        lastname = data.getString("lastname");
        id = data.getString("id");
        if (data.has("percent"))
            resourceTaskUsage = (float) data.getDouble("percent");
        else
            resourceTaskUsage = -1F;
    }

    public boolean isCreator()
    {
        return resourceTaskUsage == -1F;
    }

    public boolean isValid()
    {
        return !id.equals("");
    }

    public String getFirstname() {
        return firstname;
    }

    public void setFirstname(String firstname) {
        this.firstname = firstname;
    }

    public String getLastname() {
        return lastname;
    }

    public void setLastname(String lastname) {
        this.lastname = lastname;
    }

    public String getId() {
        return id;
    }

    public void setId(String id) {
        this.id = id;
    }

    public float getResourceTaskUsage() {
        return resourceTaskUsage;
    }

    public void setResourceTaskUsage(float resourceTaskUsage) {
        this.resourceTaskUsage = resourceTaskUsage;
    }
}
