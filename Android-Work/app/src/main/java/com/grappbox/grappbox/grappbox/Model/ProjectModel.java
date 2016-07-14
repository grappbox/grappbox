package com.grappbox.grappbox.grappbox.Model;

import android.content.Context;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.media.Image;
import android.util.Base64;
import android.util.DisplayMetrics;
import android.util.Log;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.Serializable;
import java.util.Map;

/**
 * Created by wieser_m on 24/01/2016.
 */
public class ProjectModel implements Serializable {

    private String id;
    private String name;
    private String description;
    private String phone;
    private String company;
    private byte[] logo;
    private String contact_mail;
    private String facebookURL;
    private String twitterURL;
    private String deletedAt;
    private String bugs;
    private String tasks;
    private String messages;

    public ProjectModel()
    {
        id = "";
    }

    public ProjectModel(JSONObject data) throws JSONException {
        byte[] blob = null;//Base64.decode(data.getString("project_logo"), Base64.DEFAULT);

        id = data.getString("project_id");
        name = data.getString("project_name");
        description = data.getString("project_description");
        phone = data.getString("project_phone");
        company = data.getString("project_company");
        Log.v("Logo data", data.getString("project_logo"));
        logo = null;//Base64.decode(data.getString("project_logo"), Base64.DEFAULT);
        contact_mail = data.getString("contact_mail");
        facebookURL = data.getString("facebook");
        twitterURL = data.getString("twitter");
        bugs = data.getString("number_bugs");
        tasks = data.getString("number_tasks");
        messages = data.getString("number_messages");
        JSONObject date = data.isNull("delete_at") ? null : data.getJSONObject("deleted_at");
        if (date != null)
            Log.e("WATCH", date.toString());

        deletedAt = (date == null || date.isNull("date") ? null : date.getString("date"));
    }

    public boolean isValid()
    {
        return !id.isEmpty();
    }

    public String getId() {
        return id;
    }

    public boolean isDeleted() {
        return (deletedAt != null);
    }

    public void setDeletedAt(String date)
    {
        deletedAt = date;
    }
    public String getName() {
        return name;
    }

    public String getDescription() {
        return description;
    }

    public String getPhone() {
        return phone;
    }

    public String getCompany() {
        return company;
    }

    public Bitmap getLogo(Context context) {
        return null;
//        BitmapFactory.Options opt = new BitmapFactory.Options();
//
//        DisplayMetrics metrics = context.getResources().getDisplayMetrics();
//        opt.inScreenDensity = metrics.densityDpi;
//        opt.inTargetDensity =  metrics.densityDpi;
//        opt.inDensity = DisplayMetrics.DENSITY_DEFAULT;
//        return BitmapFactory.decodeByteArray(logo, 0, logo.length, new BitmapFactory.Options());
    }

    public String getContact_mail() {
        return contact_mail;
    }

    public String getFacebookURL() {
        return facebookURL;
    }

    public String getTwitterURL() {
        return twitterURL;
    }

    public String getBugs() { return bugs; }

    public String getTasks() { return tasks; }

    public String getMessages() { return messages; }

    public void setName(String name) {
        this.name = name;
    }

    public void setDescription(String description) {
        this.description = description;
    }

    public void setPhone(String phone) {
        this.phone = phone;
    }

    public void setCompany(String company) {
        this.company = company;
    }

    public void setLogo(String logo) {
        this.logo = Base64.decode(logo, Base64.DEFAULT);
    }

    public void setContact_mail(String contact_mail) {
        this.contact_mail = contact_mail;
    }

    public void setFacebookURL(String facebookURL) {
        this.facebookURL = facebookURL;
    }

    public void setTwitterURL(String twitterURL) {
        this.twitterURL = twitterURL;
    }

    public void setBugs(String bugs){ this.bugs = bugs; }

    public void setTasks(String tasks){ this.tasks = tasks; }

    public void setMessages(String messages){ this.messages = messages; }
}
