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

If you provide a `srcset` attribute, but no `sizes` attribute, a default
`sizes` attribute will be added, suitable for a standard 700px wide image.

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

    def make_img
        html = ''
        webp_source_attrs = []

        # Attributes for the <img> tag.
        @args.each do |key, value|
            html = '%s %s="%s"' % [ html, key, value ]
        end

        # The <img> tag itself.
        html = '<img %s>' % [ html ]

        # If the provided src attribute points to a non-webp image file,
        # and there is an identically named webp file on disk, store a new
        # src attribute for an alternative <source> element.
        if @args["src"] and not /[.]webp/i.match( @args["srcset"])
            webp_path = @args["src"].gsub(/([.]\w+)$/i, '.webp')
            if file_exists(webp_path)
                webp_source_attrs.push(
                    'src="%s"' % [ webp_path ]
                )
            end
        end

        # If the provided srcset attribute does not point to webp image files,
        # but there are identically named webp files on disk, store new srcset
        # and sizes attributes for an alternative <source> element.
        if @args["srcset"] and not /[.]webp/i.match( @args["srcset"])
            webp_images_found = 0
            webp_paths = @args["srcset"].scan(/(\S+)(?:[.]\w+)/i).flatten.map do |s|
                "%s.webp" % [ s ]
            end
            webp_paths.each do |webp_path|
                if file_exists(webp_path)
                    webp_images_found += 1
                end
            end
            # Only autogenerate a webp srcset if we have found *all* the required
            # webp alternative files.
            if webp_paths.count == webp_images_found
                webp_srcset = @args["srcset"].gsub(/([.]\w+)/i, '.webp')
                webp_source_attrs.push(
                    'srcset="%s"' % [ webp_srcset ]
                )
                # Also add sizes attribute, to match the <img>.
                if @args["sizes"]
                    webp_source_attrs.push(
                        'sizes="%s"' % [ @args["sizes"] ]
                    )
                end
            end
        end

        # The <picture> and <source> element, if webp alternatives were found.
        if webp_source_attrs.any?
            html = '<picture><source type="image/webp" %s>%s</picture>' % [ webp_source_attrs.join(" "), html ]
        end

        return html
    end

    def add_attributes(pairs)
        pairs.each do |key, value|
            @args[ key ] = strip_quotes(value)
        end

        if @args.has_key?("srcset") and not @args.has_key?("sizes")
            @args[ "sizes" ] = "(min-width: 730px) 700px, 100vw"
        end
    end

    def to_string
        html = make_img
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
