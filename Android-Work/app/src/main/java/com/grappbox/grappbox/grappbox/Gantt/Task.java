package com.grappbox.grappbox.grappbox.Gantt;

import android.util.Pair;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.GregorianCalendar;
import java.util.List;

/**
 * Created by wieser_m on 09/03/2016.
 */
public class Task {

    public enum ELinkType
    {
        NONE,
        START_TO_START,
        END_TO_START,
        START_TO_END,
        END_TO_END
    }

    //Gantt view usage
    private String id;
    private int accomplishedPercent;
    private Date startDate, endDate;
    private String title;
    private List<Pair<String, ELinkType>> links;
    private boolean isMilestone;
    private boolean isContainer;

    //Task Usage only
    private String description;
    private TaskUser creator;
    private ArrayList<TaskUser> users;
    private ArrayList<TaskTag> tags;

    public Task() {
        id = "";
        links = new ArrayList<>();
        isMilestone = false;
    }

    public Task(String id, Date startDate, Date endDate, String title, List<Pair<String, ELinkType>> links, boolean bIsMilestone, boolean bIsContainer, int accomplishedPercent) {
        this.id = id;
        this.startDate = startDate;
        this.endDate = endDate;
        this.title = title;
        this.links = links;
        this.isMilestone = bIsMilestone;
        this.isContainer = bIsContainer;
        this.accomplishedPercent = accomplishedPercent;
        if (this.links == null)
            this.links = new ArrayList<>();
    }

    public Task(JSONObject data) throws JSONException, ParseException {
        if (this.links == null)
            this.links = new ArrayList<>();
        SimpleDateFormat format = new SimpleDateFormat("yyyy-MM-dd");
        JSONArray jusersAssigned = data.getJSONArray("users_assigned");
        JSONArray jtags = data.getJSONArray("tags");
        JSONArray jdependencies = data.getJSONArray("dependencies");

        id = data.getString("id");
        title = data.getString("title");
        description = data.getString("description");
        endDate = format.parse(data.getJSONObject("due_date").getString("date"));
        startDate = format.parse(data.getJSONObject("started_at").getString("date"));
        isMilestone = data.getBoolean("is_milestone");
        for (int i = 0; i < jusersAssigned.length(); ++i)
            users.add(new TaskUser(jusersAssigned.getJSONObject(i)));
        for (int i = 0; i < jtags.length(); ++i)
            tags.add(new TaskTag(jtags.getJSONObject(i)));
        for (int i = 0; i < jdependencies.length(); ++i)
        {
            JSONObject current = jdependencies.getJSONObject(i);
            ELinkType type = ELinkType.NONE;
            switch (current.getString("name"))
            {
                case "fs":
                    type = ELinkType.END_TO_START;
                    break;
                case "ss":
                    type = ELinkType.START_TO_START;
                    break;
                case "sf":
                    type = ELinkType.START_TO_END;
                    break;
                case "ff":
                    type = ELinkType.END_TO_END;
                    break;
                default:
                    break;
            }
            if (type != ELinkType.NONE)
                links.add(new Pair<>(current.getString("id"), type));
        }
    }

    public boolean IsValid()
    {
        return !id.isEmpty();
    }

    public String getId() {
        return id;
    }

    public void setId(String id) {
        this.id = id;
    }

    public Date getStartDate() {
        return startDate;
    }

    public void setStartDate(Date startDate) {
        this.startDate = startDate;
    }

    public Date getEndDate() {
        return endDate;
    }

    public void setEndDate(Date endDate) {
        this.endDate = endDate;
    }

    public String getTitle() {
        return title;
    }

    public void setTitle(String title) {
        this.title = title;
    }

    public List<Pair<String, ELinkType>> getLinks() {
        return links;
    }

    public void setLinks(List<Pair<String, ELinkType>> links) {
        this.links = links;
    }

    public Pair<String, ELinkType> getLinkAt(int index)
    {
        return links.get(index);
    }

    public void addLink(Pair<String, ELinkType> link)
    {
        links.add(link);
    }

    public void removeLink(Pair<String, ELinkType> link)
    {
        links.remove(link);
    }

    public boolean IsMilestone() {
        return isMilestone;
    }

    public boolean IsContainer() { return isContainer; }

    public void SetIsContainer(boolean isContainer) { this.isContainer = isContainer; }

    public void SetIsMilestone(boolean isMilestone) {
        this.isMilestone = isMilestone;
    }

    public int  getAccomplishedPercent(){return this.accomplishedPercent;}

    public void setAccomplishedPercent(int accomplishedPercent) { this.accomplishedPercent = accomplishedPercent; }
}
