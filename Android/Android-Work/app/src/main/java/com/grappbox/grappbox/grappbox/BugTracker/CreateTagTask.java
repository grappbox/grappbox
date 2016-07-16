package com.grappbox.grappbox.grappbox.BugTracker;

import android.content.Context;
import android.os.AsyncTask;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;

/**
 * Created by wieser_m on 02/06/2016.
 */

public class CreateTagTask extends AsyncTask<String, Void, String> {
    private CreateTagListener _listener;
    private Context _context;
    private APIConnectAdapter _api;

    public CreateTagTask(CreateTagListener _listener, Context _context) {
        this._listener = _listener;
        this._context = _context;
        _api = APIConnectAdapter.getInstance(true);
    }

    public interface CreateTagListener
    {
        void onTaskEnd(boolean success, String id);
    }

    @Override
    protected void onPostExecute(String s) {
        JSONObject json = null;
        JSONObject info = null;
        JSONObject data = null;

        super.onPostExecute(s);

        if (s == null)
        {
            if (_listener != null)
                _listener.onTaskEnd(false, null);
            return;
        }
        try {
            json = new JSONObject(s);
            info = json.getJSONObject("info");
            data = json.getJSONObject("data");
            if (_listener != null)
                _listener.onTaskEnd(!BugtrackerInfoHandler.process(_context, _api.getResponseCode(), info), data.getString("id"));
        } catch (IOException | JSONException e) {
            e.printStackTrace();
        }
    }

    @Override
    protected String doInBackground(String... params) {
        if (params.length < 1)
            return null;
        String token = SessionAdapter.getInstance().getToken();
        String currentProjectId = SessionAdapter.getInstance().getCurrentSelectedProject();

        try {
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();
            data.put("token", token);
            data.put("name", params[0]);
            data.put("projectId", currentProjectId);
            json.put("data", data);
            _api.startConnection("bugtracker/tagcreation");
            _api.setRequestConnection("POST");
            _api.sendJSON(json);
            return _api.getInputSream();
        } catch (IOException | JSONException e) {
            e.printStackTrace();
        }
        return null;
    }
}
