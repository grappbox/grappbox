package com.grappbox.grappbox.grappbox.Model;

import org.json.JSONException;
import org.json.JSONObject;

/**
 * Created by wieser_m on 27/01/2016.
 */
public class RoleModel {
    public enum EAccess
    {
        NONE(0),
        READ(1),
        READ_WRITE(2);

        private int value;
        private EAccess(int value)
        {
            this.value = value;
        }
        public int toInt()
        {
            return value;
        }
    }

    int id;
    String name;
    EAccess team_timeline;
    EAccess customer_timeline;
    EAccess gantt;
    EAccess whiteboard;
    EAccess bugtracker;
    EAccess event;
    EAccess task;
    EAccess project_settings;
    EAccess cloud;

    public RoleModel() {
        id = -1;
        name = "";
        team_timeline = EAccess.NONE;
        customer_timeline = EAccess.NONE;
        gantt = EAccess.NONE;
        whiteboard = EAccess.NONE;
        bugtracker = EAccess.NONE;
        event = EAccess.NONE;
        task = EAccess.NONE;
        project_settings = EAccess.NONE;
        cloud = EAccess.NONE;
    }

    public EAccess toEAccess(int access)
    {
        if (access >= 2)
            return EAccess.READ_WRITE;
        return (access == 0 ? EAccess.NONE : EAccess.READ);
    }

    public RoleModel(JSONObject obj) throws JSONException
    {
        id = obj.getInt("id");
        name = obj.getString("name");
        team_timeline = toEAccess(obj.getInt("team_timeline"));
        customer_timeline = toEAccess(obj.getInt("customer_timeline"));
        gantt = toEAccess(obj.getInt("gantt"));
        whiteboard = toEAccess(obj.getInt("whiteboard"));
        bugtracker = toEAccess(obj.getInt("bugtracker"));
        event = toEAccess(obj.getInt("event"));
        task = toEAccess(obj.getInt("task"));
        project_settings = toEAccess(obj.getInt("project_settings"));
        cloud = toEAccess(obj.getInt("cloud"));
    }

    public boolean isValid() { return id > 0; }

    public int getId() {
        return id;
    }

    public void setId(int id) {
        this.id = id;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public EAccess getTeam_timeline() {
        return team_timeline;
    }

    public void setTeam_timeline(EAccess team_timeline) {
        this.team_timeline = team_timeline;
    }

    public EAccess getCustomer_timeline() {
        return customer_timeline;
    }

    public void setCustomer_timeline(EAccess customer_timeline) {
        this.customer_timeline = customer_timeline;
    }

    public EAccess getGantt() {
        return gantt;
    }

    public void setGantt(EAccess gantt) {
        this.gantt = gantt;
    }

    public EAccess getWhiteboard() {
        return whiteboard;
    }

    public void setWhiteboard(EAccess whiteboard) {
        this.whiteboard = whiteboard;
    }

    public EAccess getBugtracker() {
        return bugtracker;
    }

    public void setBugtracker(EAccess bugtracker) {
        this.bugtracker = bugtracker;
    }

    public EAccess getEvent() {
        return event;
    }

    public void setEvent(EAccess event) {
        this.event = event;
    }

    public EAccess getTask() {
        return task;
    }

    public void setTask(EAccess task) {
        this.task = task;
    }

    public EAccess getProject_settings() {
        return project_settings;
    }

    public void setProject_settings(EAccess project_settings) {
        this.project_settings = project_settings;
    }

    public EAccess getCloud() {
        return cloud;
    }

    public void setCloud(EAccess cloud) {
        this.cloud = cloud;
    }
}
