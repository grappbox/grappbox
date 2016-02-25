package com.grappbox.grappbox.grappbox.BugTracker;

import android.content.Context;
import android.os.AsyncTask;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.LinearLayout;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;
import com.grappbox.grappbox.grappbox.R;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;

/**
 * Created by wieser_m on 19/02/2016.
 */
public class GetBugTagTask extends AsyncTask<String, Void, String> {
    private Context _context;
    private LinearLayout _adapter;
    private APIConnectAdapter _api;
    private OnTaskListener _listener;

    public GetBugTagTask(Context context, LinearLayout adapter, OnTaskListener listener)
    {
        _context = context;
        _adapter = adapter;
        _api = APIConnectAdapter.getInstance(true);
        _api.setVersion("V0.2");
        _listener = listener;
    }

    @Override
    protected String doInBackground(String... params) {
        String token = SessionAdapter.getInstance().getToken();
        String projectId = String.valueOf(SessionAdapter.getInstance().getCurrentSelectedProject());

        try {
            _api.startConnection("bugtracker/getprojecttags/" + token + "/" + projectId);
            _api.setRequestConnection("GET");
            return _api.getInputSream();
        } catch (IOException e) {
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
            if (BugtrackerInfoHandler.process(_context, _api.getResponseCode(), info) || data == null)
                return;
            JSONArray array = data.getJSONArray("array");
            if (array != null)
            {
                for (int i = 0; i < array.length(); ++i)
                {
                    JSONObject current = array.getJSONObject(i);
                    View lay = LayoutInflater.from(_context).inflate(R.layout.li_checkable_item, null);
                    BugIdCheckbox cb = (BugIdCheckbox) lay.findViewById(R.id.cb_assigned);
                    cb.setText(current.getString("name"));
                    cb.SetId(current.getString("id"));
                    _adapter.addView(lay);
                }
                if (_listener != null)
                    _listener.OnTaskEnd(true, data.toString());
            }
        } catch (IOException | JSONException e) {
            e.printStackTrace();
        }

        super.onPostExecute(s);
    }
}
