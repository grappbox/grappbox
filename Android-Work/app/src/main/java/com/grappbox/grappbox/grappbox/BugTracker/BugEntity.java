package com.grappbox.grappbox.grappbox.BugTracker;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by wieser_m on 18/02/2016.
 */
public class BugEntity {
    public static final String EXTRA_GRAPPBOX_BUG_ID = "extra.grappbox.bugId";

    String              _id;
    String              _projectId;
    String              _title;
    String              _description;
    String              _parentId;
    String              _createdAt;
    String              _editedAt;
    String              _deletedAt;
    String              _creatorId;
    String              _creatorFullname;
    String              _stateId;
    String              _stateName;
    List<TagEntity>     _tags;
    List<UserEntity>    _users;


    public BugEntity()
    {
        _id = "";
        _tags = new ArrayList<>();
        _users = new ArrayList<>();
    }

    public BugEntity(JSONObject data) throws JSONException {
        _tags = new ArrayList<>();
        _users = new ArrayList<>();
        reimport(data);
    }

    public boolean IsValid()
    {
        return !_id.isEmpty();
    }

    public void reimport(JSONObject data) throws JSONException
    {
        JSONObject creator = data.getJSONObject("creator");
        JSONObject createdAt = data.getJSONObject("createdAt");
        JSONObject editedAt = (data.has("editedAt") && !data.isNull("editedAt") ? data.getJSONObject("editedAt") : null);
        JSONObject deletedAt = (data.has("deletedAt") && !data.isNull("deletedAt") ? data.getJSONObject("deletedAt") : null);
        JSONArray tags = data.getJSONArray("tags");
        JSONArray users = data.getJSONArray("users");
        int max = (tags.length() > users.length() ? tags.length() : users.length());

        _id = data.getString("id");
        _projectId = data.getString("projectId");
        _title = data.getString("title");
        _description = data.getString("description");
        _parentId = data.getString("parentId");
        _createdAt = createdAt.getString("date");
        _editedAt = (editedAt == null ? null : editedAt.getString("date"));
        _deletedAt = (deletedAt == null ? null : deletedAt.getString("date"));
        _creatorId = creator.getString("id");
        _creatorFullname = creator.getString("fullname");

        for (int i = 0; i < max; ++i)
        {
            if (i < tags.length())
                _tags.add(new TagEntity(tags.getJSONObject(i)));
            if (i < users.length())
                _users.add(new UserEntity(users.getJSONObject(i)));
        }
    }

    public String GetId() {
        return _id;
    }

    public void SetId(String _id) {
        this._id = _id;
    }

    public String GetProjectId() {
        return _projectId;
    }

    public void SetProjectId(String _projectId) {
        this._projectId = _projectId;
    }

    public String GetTitle() {
        return _title;
    }

    public void SetTitle(String _title) {
        this._title = _title;
    }

    public String GetDescription() {
        return _description;
    }

    public void SetDescription(String _description) {
        this._description = _description;
    }

    public String GetParentId() {
        return _parentId;
    }

    public void SetParentId(String _parentId) {
        this._parentId = _parentId;
    }

    public String GetCreatedAt() {
        return _createdAt;
    }

    public void SetCreatedAt(String _createdAt) {
        this._createdAt = _createdAt;
    }

    public String GetEditedAt() {
        return _editedAt;
    }

    public void SetEditedAt(String _editedAt) {
        this._editedAt = _editedAt;
    }

    public String GetDeletedAt() {
        return _deletedAt;
    }

    public void SetDeletedAt(String _deletedAt) {
        this._deletedAt = _deletedAt;
    }

    public String GetCreatorId() {
        return _creatorId;
    }

    public void SetCreatorId(String _creatorId) {
        this._creatorId = _creatorId;
    }

    public String GetCreatorFullname() {
        return _creatorFullname;
    }

    public void SetCreatorFullname(String _creatorFullname) {
        this._creatorFullname = _creatorFullname;
    }

    public String GetStateId() {
        return _stateId;
    }

    public void SetStateId(String _stateId) {
        this._stateId = _stateId;
    }

    public String GetStateName() {
        return _stateName;
    }

    public void SetStateName(String _stateName) {
        this._stateName = _stateName;
    }

    public List<TagEntity> GetTags() {
        return _tags;
    }

    public void GetTags(List<TagEntity> _tags) {
        this._tags = _tags;
    }

    public List<UserEntity> GetUsers() {
        return _users;
    }

    public void SetUsers(List<UserEntity> _users) {
        this._users = _users;
    }

    public boolean IsClosed() {
        return !(_deletedAt == null || _deletedAt.isEmpty());
    }
}
