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
public class GetTicketTask extends AsyncTask<String, Void, String> {
    private Context _context;
    private APIConnectAdapter _api;
    private OnTaskListener _listener;

    GetTicketTask(Context context, OnTaskListener listener)
    {
        _context = context;
        _api = APIConnectAdapter.getInstance(true);
        _listener = listener;
    }

    @Override
    protected String doInBackground(String... params) {
        if (params.length < 1)
            return null;
        String id = params[0];
        String token = SessionAdapter.getInstance().getToken();

        _api.setVersion("V0.2");
        try {
            _api.startConnection("bugtracker/getticket/" + token + "/" + id);
            _api.setRequestConnection("GET");
            return _api.getInputSream();
        } catch (IOException e) {
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
            _listener.OnTaskEnd(BugtrackerInfoHandler.process(_context, _api.getResponseCode(), info), (data != null ? data.toString() : null));
        } catch (IOException e)
        {
            e.printStackTrace();
        }
    }
}
