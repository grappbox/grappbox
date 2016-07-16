package com.grappbox.grappbox.grappbox.BugTracker;

import android.content.Context;
import android.os.AsyncTask;
import android.util.Pair;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.util.List;

/**
 * Created by wieser_m on 25/02/2016.
 */
public class SetParticipantTask extends AsyncTask<String, Void, String> {
    private Context _context;
    private OnTaskListener _listener;
    private APIConnectAdapter _api;
    private List<Pair<String, Boolean>> _ids;

    SetParticipantTask(Context context, OnTaskListener listener, List<Pair<String, Boolean>> ids)
    {
        _api = APIConnectAdapter.getInstance(true);
        _context = context;
        _listener = listener;
        _ids = ids;
    }

    @Override
    protected String doInBackground(String... params) {
        if (params.length < 1)
            return null;
        String bugId = params[0];
        JSONObject json = new JSONObject();
        JSONObject data = new JSONObject();
        JSONArray toAdd = new JSONArray();
        JSONArray toRm = new JSONArray();

        try {
            data.put("bugId", bugId);
            data.put("token", SessionAdapter.getInstance().getToken());
            for(Pair<String, Boolean> id : _ids)
            {
                if (id.second)
                    toAdd.put(id.first);
                else
                    toRm.put(id.first);
            }
            data.put("toAdd", toAdd);
            data.put("toRemove", toRm);
            json.put("data", data);
            _api.setVersion("V0.2");
            _api.startConnection("bugtracker/setparticipants");
            _api.setRequestConnection("PUT");
            _api.sendJSON(json);
            return _api.getInputSream();
        } catch (JSONException | IOException e) {
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
