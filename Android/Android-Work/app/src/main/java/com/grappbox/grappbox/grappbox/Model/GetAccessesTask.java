package com.grappbox.grappbox.grappbox.Model;

import android.content.Context;
import android.os.AsyncTask;

import com.grappbox.grappbox.grappbox.BugTracker.BugtrackerInfoHandler;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;

/**
 * Created by wieser_m on 18/06/2016.
 */
public class GetAccessesTask extends AsyncTask<Void, Void, String> {
    private APIConnectAdapter   _api;
    private TaskListener        _listener;
    private Context             _context;
    private Integer             _value;

    public GetAccessesTask(TaskListener _listener, Context _context) {
        this._listener = _listener;
        this._context = _context;
    }

    @Override
    protected String doInBackground(Void... params) {
        String token = SessionAdapter.getInstance().getToken();
        String userId = SessionAdapter.getInstance().getUserID();
        _api = APIConnectAdapter.getInstance(true);
        try {
            _api.startConnection("roles/getuserrolesinformations/" + token + "/" + userId);
            _api.setRequestConnection("GET");
            _value = _api.getResponseCode();
            return _api.getInputSream();
        } catch (IOException e) {
            e.printStackTrace();
        }

        return null;
    }

    @Override
    protected void onPostExecute(String s) {
        super.onPostExecute(s);
        if (s == null)
        {
            if (_listener != null)
                _listener.onTaskFetched(false, null);
            return;
        }
        JSONObject json, data, info;
        try {
            json = new JSONObject(s);
            data = json.getJSONObject("data");
            info = json.getJSONObject("info");
            if (BugtrackerInfoHandler.process(_context, _api.getResponseCode(), info))
            {
                if (_listener != null)
                    _listener.onTaskFetched(false, null);
                return;
            }
            _listener.onTaskFetched(true, data);
        } catch (IOException | JSONException e) {
            e.printStackTrace();
            if (_listener != null)
                _listener.onTaskFetched(false, null);
            return;
        }
        _api.closeConnection();
    }

    public interface TaskListener{
        void onTaskFetched(boolean success, JSONObject data);
    }
}
