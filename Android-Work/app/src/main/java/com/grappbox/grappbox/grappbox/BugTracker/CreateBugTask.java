package com.grappbox.grappbox.grappbox.BugTracker;

import android.app.Activity;
import android.content.Intent;
import android.os.AsyncTask;
import android.widget.EditText;
import android.widget.LinearLayout;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;
import com.grappbox.grappbox.grappbox.R;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.util.ArrayList;
import java.util.List;

/**
 * Created by wieser_m on 19/02/2016.
 */
public class CreateBugTask extends AsyncTask<String, Void, String> {
    LinearLayout _root;
    LinearLayout _layTag;
    LinearLayout _layUser;
    List<String> _tagList;
    List<String> _userList;
    String _title;
    String _description;
    APIConnectAdapter _api;
    String _bugId;
    Activity _context;

    public CreateBugTask(Activity context, LinearLayout rootLayout) {
        _root = rootLayout;
        _layTag = ((LinearLayout) rootLayout.findViewById(R.id.ll_categories));
        _layUser = (LinearLayout) rootLayout.findViewById(R.id.ll_assignee);
        _title = ((EditText) rootLayout.findViewById(R.id.et_title)).getText().toString();
        _description = ((EditText) rootLayout.findViewById(R.id.et_description)).getText().toString();
        _tagList = new ArrayList<>();
        _userList = new ArrayList<>();
        for (int i = 0; i < _layTag.getChildCount(); ++i) {
            BugIdCheckbox cb = ((BugIdCheckbox) _layTag.getChildAt(i).findViewById(R.id.cb_assigned));
            if (cb.isChecked())
                _tagList.add(cb.GetStoredId());
        }
        for (int i = 0; i < _layUser.getChildCount(); ++i) {
            BugIdCheckbox cb = ((BugIdCheckbox) _layUser.getChildAt(i).findViewById(R.id.cb_assigned));
            if (cb.isChecked())
                _userList.add(cb.GetStoredId());
        }
        _api = APIConnectAdapter.getInstance(true);
        _api.setVersion("V0.2");
        _bugId = "";
        _context = context;
    }

    @Override
    protected String doInBackground(String... params) {
        JSONObject json = new JSONObject();
        JSONObject data = new JSONObject();
        try {
            data.put("token", SessionAdapter.getInstance().getToken());
            data.put("projectId", SessionAdapter.getInstance().getCurrentSelectedProject());
            data.put("title", _title);
            data.put("description", _description);
            data.put("stateId", 1);
            data.put("stateName", "");
            json.put("data", data);
            _api.startConnection("bugtracker/postticket");
            _api.setRequestConnection("POST");
            _api.sendJSON(json);
            return _api.getInputSream();
        } catch (IOException | JSONException e) {
            e.printStackTrace();
        }

        return null;
    }

    @Override
    protected void onPostExecute(String s) {
        JSONObject json, info = null;
        JSONObject data = null;
        if (s != null) {
            try {
                json = new JSONObject(s);
                info = json.getJSONObject("info");
                data = json.getJSONObject("data");
            } catch (JSONException e) {
                e.printStackTrace();
            }
        }
        super.onPostExecute(s);
        try {
            if (BugtrackerInfoHandler.process(_root.getContext(), _api.getResponseCode(), info) || data == null)
                return;
            _bugId = data.getString("id");
            for (String id : _tagList) {
                BugAssignTagTask battask = new BugAssignTagTask();
                battask.execute(id);
            }
            BugAssignUsersTask bautask = new BugAssignUsersTask();
            bautask.execute();
        } catch (IOException | JSONException e) {
            e.printStackTrace();
        }
    }

    private class BugAssignTagTask extends AsyncTask<String, Void, String>
    {
        private APIConnectAdapter _taskAPI;
        @Override
        protected void onPostExecute(String s) {
            super.onPostExecute(s);
        }

        @Override
        protected String doInBackground(String... params) {
            if (params.length < 1)
                return null;
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();

            _taskAPI = APIConnectAdapter.getInstance(true);
            _taskAPI.setVersion("V0.2");
            try {
                data.put("token", SessionAdapter.getInstance().getToken());
                data.put("bugId", _bugId);
                data.put("tagId", params[0]);
                json.put("data", data);
                _taskAPI.startConnection("bugtracker/assigntag");
                _taskAPI.setRequestConnection("PUT");
                _taskAPI.sendJSON(json);
                return _taskAPI.getInputSream();
            } catch (IOException | JSONException e) {
                e.printStackTrace();
            }

            return null;
        }
    }

    private class BugAssignUsersTask extends AsyncTask<String, Void, String>
    {
        APIConnectAdapter _userAPI;

        @Override
        protected void onPostExecute(String s) {
            super.onPostExecute(s);
            _context.finish();
        }

        @Override
        protected String doInBackground(String... params) {
            if (params.length < 1)
                return null;
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();
            JSONArray toAdd = new JSONArray();

            _userAPI = APIConnectAdapter.getInstance(true);
            _userAPI.setVersion("V0.2");
            try {
                data.put("token", SessionAdapter.getInstance().getToken());
                data.put("bugId", _bugId);
                for (String id : _userList)
                {
                    toAdd.put(id);
                }
                data.put("toRemove", new JSONArray());
                data.put("toAdd", toAdd);
                json.put("data", data);
                _userAPI.startConnection("bugtracker/setparticipants");
                _userAPI.setRequestConnection("PUT");
                _userAPI.sendJSON(json);
                return _userAPI.getInputSream();
            } catch (IOException | JSONException e) {
                e.printStackTrace();
            }

            return null;
        }
    }

}
