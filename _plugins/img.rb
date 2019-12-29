=begin

Example usage:

    {% img src="/media/this-is-the-image.jpg" alt="This is the alt text" width="700" height="350" %}

Arguments can go in any order, and can use double, single, or (as long as the
argument value contains no spaces) no quotes at all, just like HTML5.

Images will have an empty `alt` attribute and lazy-loading boilerplate
added automatically. As such, the simplest img tag would be:

    {% img src="/media/this-is-the-image.jpg" %}

Which would produce the following output:

    <noscript class="loading-lazy"><img src="/media/this-is-the-image.jpg" alt="" loading="lazy"></noscript>

For historical reasons, a simplified format is also accepted,
as long as you only want to set `alt` and `src` attributes,
in that exact order:

    {% img "This is the alt text" /media/this-is-the-image.jpg %}

If you want to use Liquid variables as arguments, try blockimg:

    {% blockimg %}
        alt="{{ post.title }}"
        src="{{ post.image | relative_url }}"
        class="rounded"
    {% endblockimg %}

The blockimg tag also accepts the simplified two-attribute syntax:

    {% blockimg %}
        "{{ post.title }}"
        "{{ post.image | relative_url }}"
    {% endblockimg %}

=end

def scan_for_image_attributes(markup)
    return markup.scan(/((?:src|srcset|sizes|width|height|alt|class|style|loading))=("[^"]+"|'[^']+'|\S+)/)
end

def extract_attributes_from_csv(markup)
    matches = CSV::parse_line(markup.strip, col_sep: ' ')
    return { "src" => matches[1], "alt" => matches[0] }
end

def strip_quotes(string)
    string = string.strip
    if string.start_with?('"') and string.end_with?('"')
        return string[1...-1]
    elsif string.start_with?("'") and string.end_with?("'")
        return string[1...-1]
    else
        return string
    end
end

def file_exists(path)
    return File.exist?(path.gsub(/^\//, ''))
end

class Img
    def initialize
        @args = { "loading" => "lazy", "alt" => "" }
    end

    def add_attributes(pairs)
        pairs.each do |key, value|
            @args[ key ] = strip_quotes(value)
        end

        # Automagically detect high-res ("-1400") counterparts on disk.
        unless @args.key?("srcset")
            high_res_path = @args["src"].gsub(/([.]\w+)$/, '-1400\1')
            if file_exists(high_res_path)
                @args["srcset"] = "%s 700w, %s 1400w" % [ @args["src"], high_res_path ]
                @args["sizes"] = "(min-width: 730px) 700px, 100vw"
            end
        end
    end

    def to_string
        html = ''
        @args.each do |key, value|
            html = '%s %s="%s"' % [ html, key, value ]
        end
        html = '<img %s>' % [ html ]
        if @args["loading"] == "lazy"
            html = '<noscript class="loading-lazy">%s</noscript>' % [ html ]
        end
        return html
    end
end

module Jekyll
    class ImgTag < Liquid::Tag
        def initialize(tag_name, markup, tokens)
            super
            @img = Img.new
            matches = scan_for_image_attributes(markup)
            if matches.empty?
                matches = extract_attributes_from_csv(markup)
            end
            @img.add_attributes(matches)
        end

        def render(context)
            return @img.to_string
        end
    end

    class BlockImgTag < Liquid::Block
        def render(context)
            markup = super.gsub(/\s+/, ' ')
            @img = Img.new
            matches = scan_for_image_attributes(markup)
            if matches.empty?
                matches = extract_attributes_from_csv(markup)
            end
            @img.add_attributes(matches)

            return @img.to_string
        end
    end
end

Liquid::Template.register_tag('img', Jekyll::ImgTag)
Liquid::Template.register_tag('blockimg', Jekyll::BlockImgTag)
