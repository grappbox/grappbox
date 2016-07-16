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

public class ReopenTicketTask extends AsyncTask<String, Void, String> {
    Context _context;
    APIConnectAdapter _api;
    OnTaskListener _listener;
    String _id = "";

    public ReopenTicketTask(Context context, OnTaskListener listener)
    {
        _context = context;
        _listener = listener;
        _api = APIConnectAdapter.getInstance(true);
    }

    @Override
    protected String doInBackground(String... params) {
        if (params.length < 1)
            return null;
        String token = SessionAdapter.getInstance().getToken();

        _id = params[0];
        _api.setVersion("V0.2");
        try {
            _api.startConnection("bugtracker/reopenticket/" + token + "/" + _id);
            _api.setRequestConnection("PUT");
            return _api.getInputSream();
        } catch (IOException e) {
            e.printStackTrace();
        }
        return null;
    }

    @Override
    protected void onPostExecute(String s) {
        JSONObject json, info;
        json = info = null;
        super.onPostExecute(s);

        if (s != null)
        {
            try {
                json = new JSONObject(s);
                info = json.getJSONObject("info");
            } catch (JSONException e) {
                e.printStackTrace();
            }
        }
        try
        {
            _listener.OnTaskEnd(BugtrackerInfoHandler.process(_context, _api.getResponseCode(), info), _id);
        } catch (IOException e)
        {
            e.printStackTrace();
        }

    }
}
