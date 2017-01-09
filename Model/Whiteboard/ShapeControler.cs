﻿using Grappbox.ViewModel;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Windows.Foundation;
using Windows.UI;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Input;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Shapes;

namespace Grappbox.Model
{

    public class ShapeControler
    {
        public int Id { get; set; }
        public int Index { get; set; }
        private WhiteboardTool type;
        public WhiteboardTool Type
        {
            get { return type; }
        }
        private ICustomShape customShape;

        public UIElement UiElem
        {
            get { return customShape.GetElement(); }
        }

        private Point _posOrigin;
        public Point PosOrigin
        {
            get { return _posOrigin; }
            set { _posOrigin = value; }
        }

        public Color StrokeColor { get; set; }

        public Color FillColor { get; set; }

        public Point PosEnd { get; set; }

        public double Lineweight { get; set; }

        public ShapeControler(Point pos, string txt, bool bold, bool italic, SolidColorBrush stroke, int size)
        {
            StrokeColor = stroke.Color;
            customShape = new CustomText();
            customShape.Initialize(txt, bold, italic, stroke, size);
            this.type = WhiteboardTool.TEXT;
            PosOrigin = pos;
        }

        public ShapeControler(WhiteboardTool type, Point pos, SolidColorBrush stroke, SolidColorBrush fill, double thickness)
        {
            Lineweight = thickness;
            FillColor = fill.Color;
            StrokeColor = stroke.Color;
            PosOrigin = pos;
            this.type = type;
            switch (type)
            {
                case WhiteboardTool.RECTANGLE:
                    customShape = new CustomRectangle();
                    break;
                case WhiteboardTool.ELLIPSE:
                    customShape = new CustomEllipse();
                    break;
                case WhiteboardTool.LINE:
                    customShape = new CustomLine();
                    break;
                case WhiteboardTool.HANDWRITING:
                    customShape = new Pen();
                    break;
                case WhiteboardTool.LOZENGE:
                    customShape = new Lozenge();
                    break;
            }
            customShape.Initialize(pos, stroke, fill, thickness);
        }
        public ShapeControler(WhiteboardTool type, PointCollection pos, SolidColorBrush stroke, SolidColorBrush fill, double thickness)
        {
            PosOrigin = pos[0];
            this.type = type;
            customShape = new Pen();
            customShape.Initialize(pos, stroke, fill, thickness);
        }
        public void Update(Point p)
        {
            PosEnd = p;
            customShape.Update(p);
        }
        public static double AbsoluteDiff(double d1, double d2)
        {
            return (d1 - d2) > 0 ? (d1 - d2) : -(d1 - d2);
        }
    }
}
