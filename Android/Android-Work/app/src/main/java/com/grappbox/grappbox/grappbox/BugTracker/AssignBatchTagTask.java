package com.grappbox.grappbox.grappbox.BugTracker;

import android.content.Context;
import android.os.AsyncTask;
import android.telecom.Call;
import android.util.Log;
import android.util.Pair;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.lang.reflect.Array;
import java.net.ProtocolException;
import java.util.ArrayList;
import java.util.List;

/**
 * Created by wieser_m on 25/02/2016.
 */
public class AssignBatchTagTask extends AsyncTask<String, Void, String> {
    private Context _context;
    private OnTaskListener _listener;
    private ArrayList<String> _ids;
    private List<Pair<String, Boolean>> _updateIds;

    AssignBatchTagTask(Context context, OnTaskListener listener, List<Pair<String, Boolean>> updateIds)
    {
        _context = context;
        _listener = listener;
        _updateIds = updateIds;
        _ids = new ArrayList<String>();
    }

    private void CallAssign(String id, String bugId)
    {
        APIConnectAdapter api = APIConnectAdapter.getInstance(true);
        JSONObject json = new JSONObject();
        JSONObject data = new JSONObject();
        String result;

        try {
            data.put("token", SessionAdapter.getInstance().getToken());
            data.put("bugId", bugId);
            data.put("tagId", id);
            json.put("data", data);
            api.setVersion("V0.2");
            api.startConnection("bugtracker/assigntag");
            api.setRequestConnection("PUT");
            api.sendJSON(json);
            result = api.getInputSream();
            if (result == null || result.isEmpty())
                return;
            json = new JSONObject(result);
            data = json.getJSONObject("info");
            if (BugtrackerInfoHandler.process(_context, api.getResponseCode(), data))
                return;
            _ids.add(id);
        } catch (JSONException | IOException e) {
            e.printStackTrace();
        }

    }

    private void CallUnAssign(String id, String bugId)
    {

        APIConnectAdapter api = APIConnectAdapter.getInstance(true);
        String result;
        JSONObject json, data;
        try {
            api.setVersion("V0.2");
            api.startConnection("bugtracker/removetag/" + SessionAdapter.getInstance().getToken() + "/" + bugId + "/" + id);
            api.setRequestConnection("DELETE");
            api.getInputSream();
            result = api.getInputSream();
            if (result == null || result.isEmpty())
                return;
            json = new JSONObject(result);
            data = json.getJSONObject("info");
            if (BugtrackerInfoHandler.process(_context, api.getResponseCode(), data))
                return;
            _ids.add(id);
        } catch (IOException | JSONException e) {
            e.printStackTrace();
        }
    }

    @Override
    protected String doInBackground(String... params) {
        if (params.length < 1)
            return null;
        String bugId = params[0];
        for (Pair<String, Boolean> currentId : _updateIds)
        {
            if (currentId.second)
                CallAssign(currentId.first, bugId);
            else
                CallUnAssign(currentId.first, bugId);
        }
        return "";
    }

    @Override
    protected void onPostExecute(String s) {
        super.onPostExecute(s);
        String[] array = _ids.toArray(new String[_ids.size()]);


        _listener.OnTaskEnd(s == null, array);
    }
}
