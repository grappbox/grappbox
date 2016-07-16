package com.grappbox.grappbox.grappbox.BugTracker;

import android.util.Log;

import org.json.JSONException;
import org.json.JSONObject;

/**
 * Created by wieser_m on 25/02/2016.
 */
public class BugCommentEntity {
    private String id;
    private String authorName;
    private String authorId;
    private String date;
    private String title;
    private String content;

    BugCommentEntity()
    {
        id = "";
    }

    BugCommentEntity(JSONObject data) throws JSONException
    {
        reimport(data);
    }

    public void reimport(JSONObject data) throws JSONException
    {

        JSONObject creator = data.getJSONObject("creator");
        JSONObject jDate = data.getJSONObject("createdAt");

        id = data.getString("id");
        authorName = creator.getString("fullname");
        authorId = creator.getString("id");
        date = jDate.getString("date").substring(0, 19);
        title = data.getString("title");
        content = data.getString("description");
    }

    public boolean IsValid()
    {
        return (!id.isEmpty());
    }

    public String getId() {
        return id;
    }

    public void setId(String id) {
        this.id = id;
    }

    public String getAuthorName() {
        return authorName;
    }

    public void setAuthorName(String authorName) {
        this.authorName = authorName;
    }

    public String getAuthorId() {
        return authorId;
    }

    public void setAuthorId(String authorId) {
        this.authorId = authorId;
    }

    public String getDate() {
        return date;
    }

    public void setDate(String date) {
        this.date = date;
    }

    public String getContent() {
        return content;
    }

    public void setContent(String content) {
        this.content = content;
    }

    public String getTitle() {
        return title;
    }

    public void setTitle(String title) {
        this.title = title;
    }
}
