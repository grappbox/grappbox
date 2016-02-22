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
 * Created by wieser_m on 19/02/2016.
 */
public class GetLastTicketsTask extends AsyncTask<String, Void, String>{

    private int _offset, _limit;
    private APIConnectAdapter   _api;
    private boolean _needClear;
    private BugListAdapter _adapter;
    private Context _context;
    private SwipeRefreshLayout _swiper;
    private boolean _closed;

    public GetLastTicketsTask(Context context, BugListAdapter adapter, boolean needClear, int offset, int limit, boolean... closed)
    {
        _closed = false;
        if (closed.length > 0)
            _closed = closed[0];
        _offset = offset;
        _limit = limit;
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
        String token, id, state, offset, limit;

        token = SessionAdapter.getInstance().getToken();
        id = String.valueOf(SessionAdapter.getInstance().getCurrentSelectedProject());
        state = "1";
        offset = String.valueOf(_offset);
        limit = String.valueOf(_limit);
        try {
            if (_closed)
                _api.startConnection("bugtracker/getlastclosedtickets/" + token + "/" + id + "/" + offset + "/" + limit);
            else
                _api.startConnection("bugtracker/getticketsbystate/" + token + "/" + id + "/" + state + "/" + offset + "/" + limit);
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
            if (BugtrackerInfoHandler.process(_context, _api.getResponseCode(), info) || data == null) {
                if (_swiper != null)
                    _swiper.setRefreshing(false);
                return;
            }
            JSONArray array = data.getJSONArray("array");
            if (array != null)
            {
                if (_needClear)
                    _adapter.clear();
                for (int i = 0; i < array.length(); ++i)
                    _adapter.add(new BugEntity(array.getJSONObject(i)));
            }
            if (_swiper != null)
                _swiper.setRefreshing(false);
        } catch (IOException | JSONException e) {
            e.printStackTrace();
        }
    }
}
