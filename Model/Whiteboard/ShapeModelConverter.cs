using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Grappbox.ViewModel;
using Grappbox.Model;
using Grappbox.Model.Whiteboard;
using Windows.UI.Xaml.Media;
using Windows.UI;
using Windows.Foundation;
using Windows.UI.Xaml.Controls;
using Windows.UI.Text;
using Windows.UI.Xaml.Shapes;

namespace Grappbox.Model.Whiteboard
{
    class ShapeModelConverter
    {

        public static string ColorToHexa(Color c)
        {
            if (c == Colors.Transparent)
                return null;
            string s = "#";
            s += c.R.ToString("X2");
            s += c.G.ToString("X2");
            s += c.B.ToString("X2");
            return s;
        }
        public static Color FromhexaToColor(string hex)
        {
            if (hex == null)
                return Colors.Transparent;
            Color color = new Color();
            hex = hex.Substring(1);
            color.A = 255;
            color.R = byte.Parse(hex.Remove(2), System.Globalization.NumberStyles.HexNumber);
            hex = hex.Substring(2);
            color.G = byte.Parse(hex.Remove(2), System.Globalization.NumberStyles.HexNumber);
            hex = hex.Substring(2);
            color.B = byte.Parse(hex, System.Globalization.NumberStyles.HexNumber);
            return color;
        }

        public static ShapeControler ModelToShape(ObjectModel om, int objectId)
        {
            if (om.Type == "RECTANGLE")
                return jsonToRectangle(om, objectId);
            else if (om.Type == "ELLIPSE")
                return jsonToEllipse(om, objectId);
            else if (om.Type == "LINE")
                return jsonToLine(om, objectId);
            else if (om.Type == "DIAMOND")
                return jsonToLozenge(om, objectId);
            else if (om.Type == "HANDWRITE")
                return jsonToHandWrite(om, objectId);
            else if (om.Type == "TEXT")
                return jsonToText(om, objectId);
            return null;
        }

        private static ShapeControler jsonToRectangle(ObjectModel om, int objectId)
        {
            SolidColorBrush strokeColor = new SolidColorBrush(FromhexaToColor(om.Color));
            SolidColorBrush fillColor = new SolidColorBrush(FromhexaToColor(om.Background));
            Point p = new Point(om.PositionStart.X, om.PositionStart.Y);
            double thickness = om.LineWeight;
            ShapeControler sc = new ShapeControler(WhiteboardTool.RECTANGLE, p, strokeColor, fillColor, thickness);
            sc.Id = objectId;
            p = new Point(om.PositionEnd.X, om.PositionEnd.Y);
            sc.Update(p);
            return sc;
        }
        private static ShapeControler jsonToEllipse(ObjectModel om, int objectId)
        {
            SolidColorBrush fillColor = new SolidColorBrush(FromhexaToColor(om.Background));
            SolidColorBrush strokeColor = new SolidColorBrush(FromhexaToColor(om.Color));
            Point p = new Point(om.PositionStart.X, om.PositionStart.Y);
            double thickness = om.LineWeight;
            ShapeControler sc = new ShapeControler(WhiteboardTool.ELLIPSE, p, strokeColor, fillColor, thickness);
            sc.Id = objectId;
            p = new Point(om.PositionEnd.X, om.PositionEnd.Y);
            sc.Update(p);
            return sc;
        }

        private static ShapeControler jsonToLine(ObjectModel om, int objectId)
        {
            SolidColorBrush fillColor = new SolidColorBrush(Colors.Transparent);
            SolidColorBrush strokeColor = new SolidColorBrush(FromhexaToColor(om.Color));
            Point p = new Point(om.PositionStart.X, om.PositionStart.Y);
            double thickness = om.LineWeight;
            ShapeControler sc = new ShapeControler(WhiteboardTool.LINE, p, strokeColor, fillColor, thickness);
            sc.Id = objectId;
            p = new Point(om.PositionEnd.X, om.PositionEnd.Y);
            sc.Update(p);
            return sc;
        }

        private static ShapeControler jsonToLozenge(ObjectModel om, int objectId)
        {
            SolidColorBrush fillColor = new SolidColorBrush(FromhexaToColor(om.Background));
            SolidColorBrush strokeColor = new SolidColorBrush(FromhexaToColor(om.Color));
            Point p = new Point(om.PositionStart.X, om.PositionStart.Y);
            double thickness = om.LineWeight;
            ShapeControler sc = new ShapeControler(WhiteboardTool.LOZENGE, p, strokeColor, fillColor, thickness);
            sc.Id = objectId;
            p = new Point(om.PositionEnd.X, om.PositionEnd.Y);
            sc.Update(p);
            return sc;
        }
        private static ShapeControler jsonToHandWrite(ObjectModel om, int objectId)
        {
            SolidColorBrush fillColor = new SolidColorBrush(Colors.Transparent);
            SolidColorBrush strokeColor = new SolidColorBrush(FromhexaToColor(om.Color));
            PointCollection pc = new PointCollection();
            foreach (Position p in om.Points)
                pc.Add(new Point(p.X, p.Y));
            double thickness = om.LineWeight;
            ShapeControler sc = new ShapeControler(WhiteboardTool.HANDWRITING, pc, strokeColor, fillColor, thickness);
            sc.Id = objectId;
            return sc;
        }
        private static ShapeControler jsonToText(ObjectModel om, int objectId)
        {
            SolidColorBrush strokeColor = new SolidColorBrush(FromhexaToColor(om.Color));
            Point p = new Point(om.PositionStart.X, om.PositionStart.Y);
            ShapeControler sc = new ShapeControler(p, om.Text, om.IsBold, om.IsItalic, strokeColor, om.Size);
            sc.Id = objectId;
            sc.Update(p);
            return sc;
        }

        public static ObjectModel ShapeToModel(ShapeControler sc)
        {
            if (sc.Type == WhiteboardTool.RECTANGLE)
                return rectangleToJson(sc);
            else if (sc.Type == WhiteboardTool.ELLIPSE)
                return ellipseToJson(sc);
            else if (sc.Type == WhiteboardTool.LINE)
                return lineToJson(sc);
            else if (sc.Type == WhiteboardTool.LOZENGE)
                return lozengeToJson(sc);
            else if (sc.Type == WhiteboardTool.HANDWRITING)
                return handwriteToJson(sc);
            else if (sc.Type == WhiteboardTool.TEXT)
                return textToJson(sc);
            return null;
        }

        private static ObjectModel rectangleToJson(ShapeControler sc)
        {
            ObjectModel om = new ObjectModel();
            om.Type = "RECTANGLE";
            om.PositionStart = new Position();
            om.PositionStart.X = sc.PosOrigin.X;
            om.PositionStart.Y = sc.PosOrigin.Y;
            om.PositionEnd = new Position();
            om.PositionEnd.X = sc.PosEnd.X;
            om.PositionEnd.Y = sc.PosEnd.Y;
            om.Color = ColorToHexa(sc.StrokeColor);
            om.Background = ColorToHexa(sc.FillColor);
            om.LineWeight = (int)sc.Lineweight;
            return om;
        }
        private static ObjectModel ellipseToJson(ShapeControler sc)
        {
            ObjectModel om = new ObjectModel();
            om.Type = "ELLIPSE";
            om.PositionStart = new Position();
            om.PositionStart.X = sc.PosOrigin.X;
            om.PositionStart.Y = sc.PosOrigin.Y;
            om.PositionEnd = new Position();
            om.PositionEnd.X = sc.PosEnd.X;
            om.PositionEnd.Y = sc.PosEnd.Y;
            om.Radius = new Position();
            om.Radius.X = Math.Abs((om.PositionEnd.X - om.PositionStart.X) / 2);
            om.Radius.Y = Math.Abs((om.PositionEnd.Y - om.PositionStart.Y) / 2);
            om.Color = ColorToHexa(sc.StrokeColor);
            om.Background = ColorToHexa(sc.FillColor);
            om.LineWeight = (int)sc.Lineweight;
            return om;
        }

        private static ObjectModel lineToJson(ShapeControler sc)
        {
            ObjectModel om = new ObjectModel();
            om.Type = "LINE";
            om.PositionStart = new Position();
            om.PositionStart.X = sc.PosOrigin.X;
            om.PositionStart.Y = sc.PosOrigin.Y;
            om.PositionEnd = new Position();
            om.PositionEnd.X = sc.PosEnd.X;
            om.PositionEnd.Y = sc.PosEnd.Y;
            om.Color = ColorToHexa(sc.StrokeColor);
            om.Background = ColorToHexa(sc.FillColor);
            om.LineWeight = (int)sc.Lineweight;
            return om;
        }

        private static ObjectModel lozengeToJson(ShapeControler sc)
        {
            ObjectModel om = new ObjectModel();
            om.Type = "DIAMOND";
            om.PositionStart = new Position();
            om.PositionStart.X = sc.PosOrigin.X;
            om.PositionStart.Y = sc.PosOrigin.Y;
            om.PositionEnd = new Position();
            om.PositionEnd.X = sc.PosEnd.X;
            om.PositionEnd.Y = sc.PosEnd.Y;
            om.Color = ColorToHexa(sc.StrokeColor);
            om.Background = ColorToHexa(sc.FillColor);
            om.LineWeight = (int)sc.Lineweight;
            return om;
        }
        private static ObjectModel handwriteToJson(ShapeControler sc)
        {
            ObjectModel om = new ObjectModel();
            om.Type = "HANDWRITE";
            om.PositionStart = new Position();
            om.PositionStart.X = sc.PosOrigin.X;
            om.PositionStart.Y = sc.PosOrigin.Y;
            om.PositionEnd = new Position();
            om.PositionEnd.X = sc.PosEnd.X;
            om.PositionEnd.Y = sc.PosEnd.Y;
            Polyline poly = sc.UiElem as Polyline;
            List<Position> lp = new List<Position>();
            foreach (Point p in poly.Points)
            {
                Position pos = new Position();
                pos.X = p.X;
                pos.Y = p.Y;
                lp.Add(pos);
            }
            om.Points = lp;
            om.Color = ColorToHexa(sc.StrokeColor);
            om.Background = ColorToHexa(sc.FillColor);
            om.LineWeight = (int)sc.Lineweight;
            return om;
        }
        private static ObjectModel textToJson(ShapeControler sc)
        {
            ObjectModel om = new ObjectModel();
            om.Type = "TEXT";
            om.PositionStart = new Position();
            om.PositionStart.X = sc.PosOrigin.X;
            om.PositionStart.Y = sc.PosOrigin.Y;
            om.PositionEnd = new Position();
            om.PositionEnd.X = sc.PosEnd.X;
            om.PositionEnd.Y = sc.PosEnd.Y;
            TextBlock tb = sc.UiElem as TextBlock;
            om.Text = tb.Text;
            om.IsBold = false;
            om.IsItalic = tb.FontStyle == FontStyle.Italic ? true : false;
            om.Color = ColorToHexa(sc.StrokeColor);
            om.Background = ColorToHexa(sc.FillColor);
            om.LineWeight = (int)sc.Lineweight;
            return om;
        }
    }
}
