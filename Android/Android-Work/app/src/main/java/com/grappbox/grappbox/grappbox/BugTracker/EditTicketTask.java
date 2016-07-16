package com.grappbox.grappbox.grappbox.BugTracker;

import android.content.Context;
import android.os.AsyncTask;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;

/**
 * Created by wieser_m on 24/02/2016.
 */
public class EditTicketTask extends AsyncTask<String, Void, String> {

    Context _context;
    OnTaskListener _listener;
    APIConnectAdapter _api;

    public EditTicketTask(Context context, OnTaskListener listener)
    {
        _context = context;
        _listener = listener;
        _api = APIConnectAdapter.getInstance(true);
    }

    @Override
    protected String doInBackground(String... params) {
        if (params.length < 3)
            return null;
        String token = SessionAdapter.getInstance().getToken();
        String bugId = params[0];
        String title = params[1];
        String description = params[2];
        JSONObject json = new JSONObject();
        JSONObject data = new JSONObject();

        _api.setVersion("V0.2");
        try {
            data.put("token", token);
            data.put("bugId", bugId);
            data.put("title", title);
            data.put("description", description);
            data.put("stateId", 1);
            data.put("stateName", "");
            data.put("clientOrigin", false);
            json.put("data", data);
            _api.startConnection("bugtracker/editticket");
            _api.setRequestConnection("PUT");
            _api.sendJSON(json);
            return _api.getInputSream();
        } catch (IOException | JSONException e) {
            e.printStackTrace();
        }
        return null;
    }

    @Override
    protected void onPostExecute(String s) {
        JSONObject json, info, data;
        json = info = data = null;
        super.onPostExecute(s);

        if (s != null)
        {
            try {
                json = new JSONObject(s);
                info = json.getJSONObject("info");
                data = json.getJSONObject("data");
            } catch (JSONException e) {
                e.printStackTrace();
            }
        }
        try
        {
            _listener.OnTaskEnd(BugtrackerInfoHandler.process(_context, _api.getResponseCode(), info), (data == null ? null : data.toString()));
        } catch (IOException e)
        {
            e.printStackTrace();
        }
    }
}
