package com.grappbox.grappbox.grappbox.BugTracker;

import android.content.Context;
import android.os.AsyncTask;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;

/**
 * Created by wieser_m on 26/02/2016.
 */
public class PostCommentTask extends AsyncTask<String, Void, String> {
    private Context _context;
    private OnTaskListener _listener;
    private APIConnectAdapter _api;

    public PostCommentTask(Context context, OnTaskListener listener) {
        _context = context;
        _listener = listener;
        _api = APIConnectAdapter.getInstance(true);
    }

    @Override
    protected String doInBackground(String... params) {
        if (params.length < 3)
            return null;
        JSONObject json = new JSONObject();
        JSONObject data = new JSONObject();

        _api.setVersion("V0.2");
        try {
            data.put("projectId", SessionAdapter.getInstance().getCurrentSelectedProject());
            data.put("token", SessionAdapter.getInstance().getToken());
            data.put("title", params[0]);
            data.put("description", params[1]);
            data.put("parentId", params[2]);
            json.put("data", data);
            _api.startConnection("bugtracker/postcomment");
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
        JSONObject json = null;
        JSONObject info = null;
        JSONObject data = null;

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
        try {
            _listener.OnTaskEnd(BugtrackerInfoHandler.process(_context, _api.getResponseCode(), info), (data == null ? null : data.toString()));
        } catch (IOException e) {
            e.printStackTrace();
        }
    }
}
