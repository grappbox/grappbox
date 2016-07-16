package com.grappbox.grappbox.grappbox.BugTracker;

import android.content.Context;
import android.os.AsyncTask;
import android.support.v4.content.ContextCompat;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;

import static android.R.attr.data;

/**
 * Created by wieser_m on 02/06/2016.
 */

public class DeleteTagTask extends AsyncTask<String, Void, String> {
    private APIConnectAdapter _api;
    private DeleteTagListener _listener;
    private Context _context;

    public interface DeleteTagListener
    {
        void onDeletionEnd(boolean success);
    }

    public DeleteTagTask(Context context, DeleteTagListener _listener) {
        this._listener = _listener;
        _api = APIConnectAdapter.getInstance(true);
        _context = context;
    }

    @Override
    protected void onPostExecute(String s) {
        JSONObject json = null;
        JSONObject info = null;

        super.onPostExecute(s);

        if (s == null)
        {
            if (_listener != null)
                _listener.onDeletionEnd(false);
            return;
        }
        try {
            json = new JSONObject(s);
            info = json.getJSONObject("info");
            if (_listener != null)
                _listener.onDeletionEnd(!BugtrackerInfoHandler.process(_context, _api.getResponseCode(), info));
        } catch (IOException | JSONException e) {
            e.printStackTrace();
        }
    }

    @Override
    protected String doInBackground(String... params) {
        if (params.length < 1)
            return null;
        String token = SessionAdapter.getInstance().getToken();
        String tagID = params[0];
        try {
            _api.startConnection("bugtracker/deletetag/" + token + "/" + tagID);
            _api.setRequestConnection("DELETE");
            return _api.getInputSream();
        } catch (IOException e) {
            e.printStackTrace();
        }
        return null;
    }
}
