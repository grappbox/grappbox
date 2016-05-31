package com.grappbox.grappbox.grappbox.BugTracker;

import android.content.Context;
import android.os.AsyncTask;
import android.support.v4.widget.SwipeRefreshLayout;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;

/**
 * Created by wieser_m on 31/05/2016.
 */

public class GetUserTicketTask extends AsyncTask<String, Void, String> {
    private APIConnectAdapter _api;
    private boolean _needClear;
    private BugListAdapter _adapter;
    private Context _context;
    private SwipeRefreshLayout _swiper;

    public GetUserTicketTask(Context context, BugListAdapter adapter, boolean needClear)
    {
        _api = APIConnectAdapter.getInstance(true);
        _adapter = adapter;
        _needClear = needClear;
        _context = context;
        _swiper = null;
        _api.setVersion("V0.2");
    }

    public void SetRefreshSwiper(SwipeRefreshLayout swiper)
    {
        _swiper = swiper;
    }

    @Override
    protected String doInBackground(String... params) {
        String token, id, user;

        token = SessionAdapter.getInstance().getUserData(SessionAdapter.KEY_TOKEN);
        id = String.valueOf(SessionAdapter.getInstance().getCurrentSelectedProject());
        user = SessionAdapter.getInstance().getUserID();

        try {
            _api.startConnection("bugtracker/getticketsbyuser/"+token+"/"+id+"/"+user);
            _api.setRequestConnection("GET");
            return _api.getInputSream();
        } catch (IOException e) {
            e.printStackTrace();
        }
        return null;
    }

    @Override
    protected void onPostExecute(String s) {
        JSONObject json;
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
            if (BugtrackerInfoHandler.process(_context, _api.getResponseCode(), info) || data == null) {
                if (_swiper != null)
                    _swiper.setRefreshing(false);
                return;
            }
            JSONArray array = data.getJSONArray("array");
            if (array != null)
            {
                if (_needClear)
                    _adapter.clearData();
                for (int i = 0; i < array.length(); ++i)
                    _adapter.insertData(new BugEntity(array.getJSONObject(i)), -1);
            }
            if (_swiper != null)
                _swiper.setRefreshing(false);
        } catch (IOException | JSONException e) {
            e.printStackTrace();
        }
    }
}
